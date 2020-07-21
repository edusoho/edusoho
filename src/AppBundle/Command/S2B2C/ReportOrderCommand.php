<?php

namespace AppBundle\Command\S2B2C;

use AppBundle\Command\BaseCommand;
use AppBundle\Common\ArrayToolkit;
use Biz\OrderFacade\Service\OrderRefundService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportOrderCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('S2B2C:report-orders')
            ->addOption('real', null, InputArgument::OPTIONAL, '是否正式执行')
            ->addOption('startTime', null, InputArgument::OPTIONAL, '订单开始筛选时间：2020-07-20')
            ->addOption('endTime', null, InputArgument::OPTIONAL, '订单结束筛选时间：2020-07-20')
            ->setDescription('查询出所有的采购订单，重新上报到s2b2c');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storageSetting = $this->getSettingService()->get('storage', []);
        $output->writeln('<info>您将为：</info>'.$storageSetting['cloud_access_key'].'重新上报所有订单');
        if ('real' !== $input->getOption('real')) {
            $output->writeln('<info>请再指令中增加 --real 参数确认执行');

            return;
        }
        $this->initServiceKernel();

        $conditions = [];
        if (!empty($input->getOption('startTime'))) {
            $conditions['start_time'] = strtotime($input->getOption('startTime'));
        }
        if (!empty($input->getOption('endTime'))) {
            $conditions['end_time'] = strtotime($input->getOption('endTime'));
        }
        $orders = $this->getOrderService()->searchOrders(
            $conditions,
            ['created_time' => 'ASC'],
            0,
            PHP_INT_MAX
        );
        $orderIds = ArrayToolkit::column($orders, 'id');
        if (empty($orderIds)) {
            $output->writeln('<info>沒有需要上报的订单</info>');
        }
        $items = ArrayToolkit::group($this->getOrderService()->searchOrderItems(['order_ids' => $orderIds], [], 0, PHP_INT_MAX), 'order_id');
        foreach ($orders as $order) {
            $order['items'] = empty($items[$order['id']]) ? [] : $items[$order['id']];
            $this->reportOrder($order, $output);
            $output->writeln('<info>orderId: '.$order['id'].'.处理完毕</info>');
        }

        $output->writeln('<info>处理完毕</info>');
    }

    protected function reportOrder($order, OutputInterface $output)
    {
        if ('success' == $order['status'] || 'finished' == $order['status']) {
            $this->onSuccessReport($order, $output);
        }

        if ('refunded' == $order['status']) {
            $this->onSuccessReport($order, $output);
            $this->onOrderRefunded($order, $output);
        }
    }

    protected function onSuccessReport($order, OutputInterface $output)
    {
        try {
            $order = $this->mockSuccessOrderData($order);
            $targetType = ArrayToolkit::column($order['items'], 'target_type');
            if (!in_array('course', $targetType)) {
                $this->getLogger()->info('[onOrderSuccess] no need report');
                $output->writeln('[onOrderSuccess] <info>orderId: '.$order['id'].'. item type is not course. no need report</info>');

                return true;
            }
            $courseIds = ArrayToolkit::column($order['items'], 'target_id');
            $courses = ArrayToolkit::index($this->getCourseService()->findCoursesByIds($courseIds), 'id');

            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $isS2b2cProduct = false;
            foreach ($order['items'] as &$item) {
                $itemProduct = $courses[$item['target_id']];
                if (!empty($item['create_extra']['s2b2cProductDetailId'])) {
                    $isS2b2cProduct = true;
                    $item['origin_product_id'] = $item['create_extra']['s2b2cProductDetailId'];
                    continue;
                }
                $product = $this->getS2b2cProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $itemProduct['id'], 'course');
                if (!empty($product['remoteResourceId'])) {
                    $isS2b2cProduct = true;
                    $item['origin_product_id'] = $product['s2b2cProductDetailId'];
                }
            }

            if (!$isS2b2cProduct) {
                $this->getLogger()->info('[onOrderSuccess] no need report');

                return true;
            }

            $this->setUserInfo($order);
            $this->geS2B2CService()->reportSuccessOrder($order, $order['items']);
            $this->getLogger()->info('[onOrderSuccess] order report succeed');
            $output->writeln('<info>[onOrderSuccess] orderId: '.$order['id'].'. order report succeed</info>');

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("[onOrderSuccess] order report failed message: {$e->getMessage()}!", ['DATA' => $order]);
            $output->writeln('<info>[onOrderSuccess] orderId: '.$order['id'].'. order report failed message: '.$e->getMessage().'! DATA:'.json_encode($order).'</info>');

            return true;
        }
    }

    protected function onOrderRefunded($order, OutputInterface $output)
    {
        $this->getLogger()->info('[onOrderRefunded] start order report');
        try {
            $targetType = ArrayToolkit::column($order['items'], 'target_type');
            if (!in_array('course', $targetType)) {
                $this->getLogger()->info('[onOrderRefunded] no need report');
                $output->writeln('<info>[onOrderRefunded] orderId: '.$order['id'].'. item type is not course. no need report</info>');

                return true;
            }
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $courseIds = ArrayToolkit::column($order['items'], 'target_id');
            $productIds = ArrayToolkit::column($this->getS2b2cProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($s2b2cConfig['supplierId'], 'course', $courseIds), 'remoteResourceId');
            $s2b2cProductDetailIds = [];
            foreach ($order['items'] as $item) {
                if (!empty($item['create_extra']['s2b2cProductDetailId'])) {
                    $s2b2cProductDetailIds[] = $item['create_extra']['s2b2cProductDetailId'];
                }
            }
            $orderRefund = $this->getOrderRefundService()->getOrderRefundById($order['items'][0]['refund_id']);
            $orderItemRefunds = $this->getBaseOrderRefundService()->findOrderItemRefundsByOrderRefundId($orderRefund['id']);

            if ((!empty($productIds) && max($productIds) > 0) || !empty($s2b2cProductDetailIds)) {
                $this->setUserInfo($order);
                $this->geS2B2CService()->reportRefundOrder($order, $orderRefund, $orderItemRefunds);
                $this->getLogger()->info('[onOrderRefunded] order report succeed');
                $output->writeln('<info>[onOrderRefunded] orderId: '.$order['id'].'. order report succeed</info>');
            }

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("[onOrderRefunded] order report failed message: {$e->getMessage()}!", ['DATA' => $order]);
            $output->writeln('<info>[onOrderRefunded] orderId: '.$order['id'].'. order report failed message: '.$e->getMessage().'! DATA'.json_encode($order).'</info>');

            return true;
        }
    }

    protected function setUserInfo(&$order)
    {
        $user = $this->getUserService()->getUser($order['user_id']);
        $order['nickname'] = $user['nickname'];
    }

    protected function mockSuccessOrderData($order)
    {
        $order['status'] = 'success';
        $items = [];
        foreach ($order['items'] as $item) {
            $item['status'] = 'success';
            $items[] = $item;
        }
        $order['items'] = $items;

        return $order;
    }

    /**
     * @return \QiQiuYun\SDK\Service\S2B2CService
     */
    protected function geS2B2CService()
    {
        return $this->getBiz()->offsetGet('qiQiuYunSdk.s2b2cService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderService
     */
    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    protected function getLogger()
    {
        return $this->getBiz()->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->createService('OrderFacade:OrderRefundService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderRefundService
     */
    protected function getBaseOrderRefundService()
    {
        return $this->createService('Order:OrderRefundService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return ProductService
     */
    protected function getS2b2cProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
