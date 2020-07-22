<?php

namespace Biz\S2B2C\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\OrderFacade\Service\OrderRefundService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use QiQiuYun\SDK\Service\S2B2CService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CourseProductSubscriber
 *
 * @codeCoverageIgnore
 * 需要通知远程的接口，忽略测试
 */
class CourseProductSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'order.success' => 'onOrderSuccess',
            'order.refunded' => 'onOrderRefunded',
            'course.marketing.update' => 'onCourseMarketingUpdate',
        ];
    }

    public function onCourseMarketingUpdate(Event $event)
    {
        $courses = $event->getSubject();
        $course = $courses['newCourse'];
        if ($this->isSupplierCourse($course)) {
            $courseProduct = $this->getS2b2cProductService()->getByTypeAndLocalResourceId('course', $course['id']);
            $this->getS2B2CService()->changeProductSellingPrice($courseProduct['s2b2cProductDetailId'], $course['price']);
        }
    }

    public function onOrderSuccess(Event $event)
    {
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        if (empty($s2b2cConfig['supplierId'])) {
            return;
        }
        $this->getLogger()->info('[onOrderSuccess] start order report');
        $context = $event->getSubject();
        try {
            $targetType = ArrayToolkit::column($context['items'], 'target_type');
            if (!in_array('course', $targetType)) {
                $this->getLogger()->info('[onOrderSuccess] no need report');

                return true;
            }
            $courseIds = ArrayToolkit::column($context['items'], 'target_id');
            $courses = ArrayToolkit::index($this->getCourseService()->findCoursesByIds($courseIds), 'id');

            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $isS2b2cProduct = false;
            foreach ($context['items'] as &$item) {
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

            $this->setUserInfo($context);
            $this->getS2B2CService()->reportSuccessOrder($context, $context['items']);
            $this->getLogger()->info('[onOrderSuccess] order report succeed');

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("[onOrderSuccess] order report failed message: {$e->getMessage()}!", ['DATA' => $context]);
            $this->getLogService()->error('order', 'course_callback', '用户支付成功，但订单与供应商结算异常！（#'.$context['id'].'）', ['error' => $e->getMessage(), 'context' => $context]);

            return true;
        }
    }

    public function onOrderRefunded(Event $event)
    {
        $this->getLogger()->info('[onOrderRefunded] start order report');
        $context = $event->getSubject();
        try {
            $targetType = ArrayToolkit::column($context['items'], 'target_type');
            if (!in_array('course', $targetType)) {
                $this->getLogger()->info('[onOrderRefunded] no need report');

                return true;
            }
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
            $courseIds = ArrayToolkit::column($context['items'], 'target_id');
            $productIds = ArrayToolkit::column($this->getS2b2cProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($s2b2cConfig['supplierId'], 'course', $courseIds), 'remoteResourceId');
            $s2b2cProductDetailIds = [];
            foreach ($context['items'] as $item) {
                if (!empty($item['create_extra']['s2b2cProductDetailId'])) {
                    $s2b2cProductDetailIds[] = $item['create_extra']['s2b2cProductDetailId'];
                }
            }
            $orderRefund = $this->getOrderRefundService()->getOrderRefundById($context['items'][0]['refund_id']);
            $orderItemRefunds = $this->getBaseOrderRefundService()->findOrderItemRefundsByOrderRefundId($orderRefund['id']);

            if ((!empty($productIds) && max($productIds) > 0) || !empty($s2b2cProductDetailIds)) {
                $this->setUserInfo($context);
                $this->getLogger()->info('[onOrderRefunded] order report succeed', [
                    'merchantOrder' => $context,
                    'merchantOrderRefund' => $orderRefund,
                    'merchantOrderRefundItems' => $orderItemRefunds,
                ]);
                $this->getS2B2CService()->reportRefundOrder($context, $orderRefund, $orderItemRefunds);
                $this->getLogger()->info('[onOrderRefunded] order report succeed');
            }

            return true;
        } catch (\Exception $e) {
            $this->getLogger()->error("[onOrderRefunded] order report failed message: {$e->getMessage()}!", ['DATA' => $context]);
            $this->getLogService()->error('order', 'course_callback', '用户退款成功，但与供应商结算异常！（#'.$context['id'].'）', ['error' => $e->getMessage(), 'context' => $context]);

            return true;
        }
    }

    protected function setUserInfo(&$order)
    {
        $user = $this->getUserService()->getUser($order['user_id']);
        $order['nickname'] = $user['nickname'];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return OrderRefundService
     */
    protected function getOrderRefundService()
    {
        return $this->getBiz()->service('OrderFacade:OrderRefundService');
    }

    /**
     * @return \Codeages\Biz\Order\Service\OrderRefundService
     */
    protected function getBaseOrderRefundService()
    {
        return $this->getBiz()->service('Order:OrderRefundService');
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
        return $this->getBiz()->service('System:LogService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function isSupplierCourse($course)
    {
        return 'supplier' === $course['platform'];
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->getBiz()->service('S2B2C:CourseProductService');
    }

    /**
     * @return S2B2CService
     */
    protected function getS2B2CService()
    {
        return $this->getS2B2CFacadeService()->getS2B2CService();
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->getBiz()->service('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return ProductService
     */
    protected function getS2b2cProductService()
    {
        return $this->getBiz()->service('S2B2C:ProductService');
    }
}
