<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\SupplierPlatformApi;
use Codeages\Biz\Order\Service\Impl\OrderServiceImpl;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class ResourceSettlementController extends BaseController
{
    private $pageSize = 20;

    public function balanceAction(Request $request)
    {
//        TODO： merchant获取
//        TODO： balanceResult获取
        $resultSet = $this->mockBalanceResult(35);
        $paginator = new Paginator($request, $resultSet['count'], $this->pageSize);

        return $this->render('admin-v2/resource-settlement/balance/index.html.twig', array(
            'paginator' => $paginator,
            'items' => $resultSet['items'],
            'merchant' => $this->mockMerchant(),
            'total' => $resultSet['count'],
            'pageSize' => $this->pageSize,
        ));
    }

    public function balanceModalAction(Request $request, $id)
    {
//        TODO： 获取detail
        $detail = $this->mockBalanceDetail($id);
        if (empty($detail)) {
            throw $this->createNotFoundException("Flow#{$id} not found");
        }

        return $this->render('admin-v2/resource-settlement/balance/modal.html.twig', array('detail' => $detail));
    }

    public function orderAction(Request $request)
    {
//        TODO： merchant获取
//        TODO： balanceResult获取
        $resultSet = $this->mockOrderResult(35);
        $paginator = new Paginator($request, $resultSet['count'], $this->pageSize);

        return $this->render('admin-v2/resource-settlement/order/index.html.twig', array(
            'paginator' => $paginator,
            'items' => $resultSet['items'],
            'merchant' => $this->mockMerchant(),
            'total' => $resultSet['count'],
            'pageSize' => $this->pageSize,
        ));
    }

    public function orderModalAction(Request $request, $sn)
    {
//        TODO： 获取detail
        $detail = $this->mockOrderDetail($sn);
        if (empty($detail)) {
            throw $this->createNotFoundException("Flow#{$sn} not found");
        }

        return $this->render('admin-v2/resource-settlement/order/modal.html.twig', array(
            'detail' => $detail,
            'merchant' => $this->mockMerchant(),
        ));
    }

    public function productAction(Request $request)
    {
        $resultSet = $this->mockProductResult(25);
        $paginator = new Paginator($request, $resultSet['count'], $this->pageSize);

        return $this->render(
            'admin-v2/resource-settlement/product.html.twig',
            array(
                'paginator' => $paginator,
                'items' => $resultSet['items'],
                'merchant' => $this->mockMerchant(),
                'total' => $resultSet['count'],
                'pageSize' => $this->pageSize,
            ));
    }

    protected function mockMerchant()
    {
        return array('supplier_name' => '供应商名称',
            'supplier_contact_name' => '联系人',
            'coop_level_name' => '合作关系',
            'supplier_mobile' => 'XXXXXXXXXXX',
            'supplier_email' => 'xxxxxxx@163.com',
            'supplier_province' => 'xx省',
            'supplier_city' => 'xx市',
            'supplier_area' => 'xx区',
            'supplier_address' => 'xx地址',
            'balance' => 10000,
            'debt_sum' => 1,
            'name' => 'merchant名',
        );
    }

    protected function mockBalanceResult($count = 30)
    {
        $actions = array('recharge', 'refund', 'purchase');
        $types = array('inflow', 'outflow');

        $balance = 1000000;
        $i = 0;
        while ($i < $count) {
            $amount = rand(1, 10000);
            $balance -= $amount;
            $result['items'][] = array(
                'id' => rand(1, 100),
                'sn' => '20200401131457546'.rand(10, 99),
                'created_time' => '1586'.rand(100000, 999999),
                'action' => $actions[array_rand($actions, 1)], //recharge purchase refund
                'title' => '测试名称购买'.rand(1, 99),
                'type' => $types[array_rand($types, 1)], //inflow outflow
                'amount' => $amount,
                'user_balance' => $balance,
            );
            ++$i;
        }

        $result['count'] = $count;

        return $result;
    }

    private function mockBalanceDetail($id)
    {
        $actions = array('recharge', 'refund', 'purchase');
        $types = array('inflow', 'outflow');

        $balance = 1000000;
        $amount = rand(1, 10000);
        $balance -= $amount;

        return array(
            'operator' => '操作人-XXX',
            'balance' => array(
                'id' => $id,
                'sn' => '20200401131457546'.rand(10, 99),
                'created_time' => '1586'.rand(100000, 999999),
                'action' => $actions[array_rand($actions, 1)], //recharge purchase refund
                'title' => '测试采购单'.rand(1, 99),
                'type' => $types[array_rand($types, 1)], //inflow outflow
                'amount' => $amount,
                'user_balance' => $balance,
            ),
            'order' => array(
                'title' => '采购单title'.rand(10, 99),
                'sn' => '20200401131457546'.rand(10, 99),
            ),
        );
    }

    protected function mockOrderResult($count = 30)
    {
        $status = array('success', 'refunded', 'purchase');

        $balance = 1000000;
        $i = 0;
        while ($i < $count) {
            $amount = rand(1, 10000);
            $balance -= $amount;
            $result['items'][] = array(
                'id' => rand(1, 100),
                'sn' => '20200401131457546'.rand(10, 99),
                'created_time' => '1586'.rand(100000, 999999),
                'status' => $status[array_rand($status, 1)], //recharge purchase refund
                'title' => '测试名称购买'.rand(1, 99),
                'coop_price' => rand(100, 9900),
                'price_amount' => $amount,
                'debt_amount' => 0,
            );
            ++$i;
        }

        $result['count'] = $count;

        return $result;
    }

    protected function mockProductResult($count = 30)
    {
        $i = 0;
        $status = array('published', 'closed');
        while ($i < $count) {
            $result['items'][] = array(
                'id' => rand(1, 100),
                'createdTime' => '1586'.rand(100000, 999999),
                'title' => '测试Product名'.rand(1, 99),
                'status' => $status[array_rand($status, 1)],
                'studentNum' => rand(1, 10),
                'maxCoursePrice' => 100,
                'suggestionPrice' => 100,
                'cooperationPrice' => 2,
            );
            ++$i;
        }

        $result['count'] = $count;

        return $result;
    }

    private function mockOrderDetail($sn)
    {
        $actions = array('recharge', 'refund', 'purchase');
        $types = array('inflow', 'outflow');

        $amount = rand(1, 10000);

        $flows = array();

        for ($i = 0; $i < 5; ++$i) {
            $flows[] = array(
                'amount' => $amount,
                'sn' => '20200401131457546'.rand(10, 99),
                'action' => $actions[array_rand($actions, 1)],
                'type' => $types[array_rand($types, 1)], //inflow outflow
                'created_time' => '1586'.rand(100000, 999999),
            );
        }

        return array(
            'order' => array(
                'id' => rand(1, 100),
                'sn' => $sn,
                'created_time' => '1586'.rand(100000, 999999),
                'status' => $actions[array_rand($actions, 1)], //recharge purchase refund
                'title' => '测试名称购买'.rand(1, 99),
                'coop_price' => rand(100, 9900),
                'price_amount' => $amount,
                'debt_amount' => 0,
                'paid_coin_amount' => 100,
                'create_extra' => array(
                    'merchant_order' => array(
                        'title' => '测试名称购买'.rand(1, 99),
                        'sn' => '20200401131457546'.rand(10, 99),
                        'nickname' => '用户'.rand(1, 10),
                    ),
                ),
            ),
            'flows' => $flows,
        );
    }

    protected function parseTableConditions(Request $request)
    {
        $conditions = $request->query->all();
        unset($conditions['page']);
        unset($conditions['pageSize']);

        $page = (int) $request->query->get('page', 1);
        $page = $page < 1 ? 1 : $page;

        $limit = (int) $request->query->get('pageSize', 20);
        $limit = $limit < 1 ? 1 : $limit;

        $start = ($page - 1) * $limit;

        return array($conditions, $start, $limit);
    }

    /**
     * @return \Biz\S2B2C\Service\ProductService
     */
    protected function getProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return SupplierPlatformApi
     */
    protected function getSupplierPlatformApi()
    {
        return $this->getBiz()->offsetGet('supplier.platform_api');
    }

    /**
     * @return \QiQiuYun\SDK\Service\S2B2CService
     */
    protected function getS2B2CService()
    {
        return $this->getBiz()->offsetGet('qiQiuYunSdk.s2b2cService');
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    /**
     * @return OrderServiceImpl
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
