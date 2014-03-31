<?php
namespace Topxia\Service\Order\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\OrderService;
use Topxia\Common\ArrayToolkit;

class OrderServiceImpl extends BaseService implements OrderService
{

    public function createOrder($order)
    {
        if (!ArrayToolkit::requireds($order, array('userId', 'title',  'amount', 'targetType', 'targetId', 'payment'))) {
            throw $this->createServiceException('创建订单失败：缺少参数。');
        }

        $order = ArrayToolkit::parts($order, array('userId', 'title', 'amount', 'targetType', 'targetId', 'payment', 'note', 'snPrefix'));

        $orderUser = $this->getUserService()->getUser($order['userId']);
        if (empty($orderUser)) {
            throw $this->createServiceException("订单用户(#{$order['userId']})不存在，不能创建订单。");
        }

        if (!in_array($order['payment'], array('none', 'alipay', 'alipaydouble', 'tenpay'))) {
            throw $this->createServiceException('创建订单失败：payment取值不正确。');
        }

        $order['sn'] = $this->generateOrderSn($order);
        unset($order['snPrefix']);

        $order['amount'] = number_format($order['amount'], 2, '.', '');
        if (intval($order['amount']*100) == 0) {
            $order['payment'] = 'none';
        }

        $order['status'] = 'created';
        $order['createdTime'] = time();

        $order = $this->getOrderDao()->addOrder($order);

        $this->_createLog($order['id'], 'created', '创建订单');

        return $order;
    }

    private function generateOrderSn($order)
    {
        $prefix = empty($order['snPrefix']) ? 'E' : (string) $order['snPrefix'];
        return  $prefix . date('YmdHis', time()) . mt_rand(10000,99999);
    }

    private function _createLog($orderId, $type, $message = '', array $data = array())
    {
        $user = $this->getCurrentUser();

        $log = array(
            'orderId' => $orderId,
            'type' => $type,
            'message' => $message,
            'data' => json_encode($data),
            'userId' => $user->id,
            'ip' => $user->currentIp,
            'createdTime' => time()
        );

        return $this->getOrderLogDao()->addLog($log);
    }

    private function getUserService()
    {
        return $this->createDao('User.UserService');
    }

    private function getOrderDao()
    {
        return $this->createDao('Order.OrderDao');
    }

    private function getOrderLogDao()
    {
        return $this->createDao('Order.OrderLogDao');
    }

}