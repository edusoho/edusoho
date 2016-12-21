<?php
namespace Tests\Cash;

use Biz\Cash\Dao\CashOrdersDao;
use Biz\Cash\Service\CashOrdersService;
use Biz\System\Service\SettingService;
use Biz\BaseTestCase;;

class CashOrdersServiceTest extends BaseTestCase
{
    public function testAddOrder()
    {
        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
        $this->assertEquals('100.00', $createOrder['amount']);

    }

    public function testGetOrderBySn()
    {
        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
        $order       = $this->getCashOrdersService()->getOrderBySn($createOrder['sn']);
        $this->assertEquals('100.00', $order['amount']);

    }

    public function testGetOrderByToken()
    {
        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time(),
            'token'       => '12345678'
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
        $order       = $this->getCashOrdersService()->getOrderByToken($createOrder['token']);
        $this->assertEquals('100.00', $order['amount']);

    }

    public function testCreatePayRecord()
    {
        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
        $this->assertEquals('100.00', $order['amount']);
        $payData = array('status' => 'closed');
        $this->getCashOrdersService()->createPayRecord($createOrder['id'], $payData);
        $result = $this->getCashOrdersService()->getOrder($createOrder['id']);
        $this->assertEquals($result['data'], json_encode($payData));

    }

    public function testCancelOrder()
    {
        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
        $this->assertEquals('100.00', $createOrder['amount']);
        $order = $this->getCashOrdersService()->cancelOrder($createOrder['id'], '取消订单');
        $this->assertEquals('cancelled', $order['status']);
    }

    public function testUpdateOrder()
    {
        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
        $this->assertEquals('100.00', $createOrder['amount']);
        $fields = array('amount' => '120.00');
        $order  = $this->getCashOrdersService()->updateOrder($createOrder['id'], $fields);
        $this->assertEquals('120.00', $order['amount']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddOrderInvalidAmount()
    {
        $this->setSettingcoin();
        $order = array(
            'sn'          => '12238551',
            'status'      => 'created',
            'title'       => 'hh',
            'amount'      => 'asd',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $createOrder = $this->getCashOrdersService()->addOrder($order);
    }

    public function testgetOrder()
    {
        $this->setSettingcoin();
        $createOrder = $this->createOrder('aaa');
        $getOrder    = $this->getCashOrdersService()->getOrder($createOrder['id']);
        $this->assertEquals('100.00', $getOrder['amount']);
    }

    public function testPayOrder()
    {
        $this->setSettingcoin();
        $createOrder = $this->createOrder('aaa');
        $payData     = array(
            'sn'       => $createOrder['sn'],
            'status'   => 'success',
            'amount'   => '100.00',
            'paidTime' => time()
        );
        list($success, $order) = $this->getCashOrdersService()->payOrder($payData);
        $this->assertEquals($success, true);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testPayOrderWhenNotExists()
    {
        $this->setSettingcoin();
        $payData = array(
            'sn' => '1'
        );
        $this->getCashOrdersService()->payOrder($payData);
    }

    public function testSearchOrders()
    {
        $this->setSettingcoin();
        $createOrder1 = $this->createOrder('aaa');
        $createOrder2 = $this->createOrder('bbb');
        $createOrder3 = $this->createOrder('ccc');

        $result1 = $this->payOrder($createOrder1['sn']);
        $result2 = $this->payOrder($createOrder2['sn']);
        $result3 = $this->payOrder($createOrder3['sn']);

        $orders = $this->getCashOrdersService()->searchOrders(array('status' => 'paid'), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);
        $this->assertEquals(count($orders), 3);
    }

    public function testSearchOrdersCount()
    {
        $this->setSettingcoin();
        $createOrder1 = $this->createOrder('aaa');
        $createOrder2 = $this->createOrder('bbb');
        $createOrder3 = $this->createOrder('ccc');
        $createOrder4 = $this->createOrder('ddd');

        $result1 = $this->payOrder($createOrder1['sn']);
        $result2 = $this->payOrder($createOrder2['sn']);
        $result3 = $this->payOrder($createOrder3['sn']);

        $ordersCount = $this->getCashOrdersService()->searchOrdersCount(array('status' => 'paid'));
        $this->assertEquals($ordersCount, 3);
    }

    public function testGetLogsByOrderId()
    {
        $this->setSettingcoin();
        $createOrder1 = $this->createOrder('aaa');
        $createOrder2 = $this->createOrder('bbb');
        $createOrder3 = $this->createOrder('ccc');

        $result1 = $this->payOrder($createOrder1['sn']);
        $result2 = $this->payOrder($createOrder2['sn']);
        $result3 = $this->payOrder($createOrder3['sn']);

        $logs1 = $this->getCashOrdersService()->getLogsByOrderId($createOrder1['id']);
        $this->assertEquals($logs1[0]['id'], 1);
        $this->assertEquals($logs1[0]['type'], 'pay_success');
    }

    public function testAnalysisAmount()
    {
        $this->setSettingcoin();
        $createOrder1 = $this->createOrder('aaa');
        $createOrder2 = $this->createOrder('bbb');
        $createOrder3 = $this->createOrder('ccc');

        $result1 = $this->payOrder($createOrder1['sn']);
        $result2 = $this->payOrder($createOrder2['sn']);
        $result3 = $this->payOrder($createOrder3['sn']);

        $amount = $this->getCashOrdersService()->analysisAmount(array('status' => 'paid'));
        $this->assertEquals($amount, '300');
    }

    public function testCloseOrders()
    {
        $this->setSettingcoin();

        $order = array(
            'sn'          => '12238551',
            'status'      => 'created',
            'title'       => 'hh',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time() - 72 * 3600
        );
        $createOrder = $this->getOrderDao()->create($order);
        $this->getCashOrdersService()->closeOrders();
        $getOrder = $this->getCashOrdersService()->getOrder($createOrder['id']);
        $this->assertEquals($getOrder['status'], 'cancelled');
    }

    public function testCanOrderPay()
    {
        $this->setSettingcoin();
        $createOrder1 = $this->createOrder('aaa');
        $result       = $this->getCashOrdersService()->canOrderPay($createOrder1);
        $this->assertEquals(true, $result);
    }

    private function payOrder($createOrderSn)
    {
        $payData = array(
            'sn'       => $createOrderSn,
            'status'   => 'success',
            'amount'   => '100.00',
            'paidTime' => time()
        );
        return $this->getCashOrdersService()->payOrder($payData);
    }

    private function createOrder($sn)
    {
        $order = array(
            'sn'          => $sn,
            'status'      => 'created',
            'title'       => $sn.'1',
            'amount'      => '100.00',
            'payment'     => 'alipay',
            'paidTime'    => time(),
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        return $this->getCashOrdersService()->addOrder($order);
    }

    private function setSettingcoin()
    {
        $coinSettingsPosted = array(
            'cash_rate' => '1.0',
            'coin_name' => 'coin'
        );
        $this->getSettingService()->set('coin', $coinSettingsPosted);

    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return CashOrdersService
     */
    protected function getCashOrdersService()
    {
        return $this->getBiz()->service('Cash:CashOrdersService');
    }

    /**
     * @return CashOrdersDao
     */
    protected function getOrderDao()
    {
        return $this->getBiz()->dao('Cash:CashOrdersDao');
    }

}
