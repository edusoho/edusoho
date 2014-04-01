<?php
namespace Topxia\Service\Order\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\OrderService;
use Topxia\Common\ArrayToolkit;

class OrderServiceImpl extends BaseService implements OrderService
{

    public function getOrder($id)
    {
        return $this->getOrderDao()->getOrder($id);
    }

    public function getOrderBySn($sn)
    {
        return $this->getOrderDao()->getOrderBySn($sn);
    }

    public function findOrdersByIds(array $ids)
    {
        $orders = $this->getOrderDao()->findOrdersByIds($ids);
        return ArrayToolkit::index($orders, 'id');
    }

    public function createOrder($order)
    {
        if (!ArrayToolkit::requireds($order, array('userId', 'title',  'amount', 'targetType', 'targetId', 'payment'))) {
            throw $this->createServiceException('创建订单失败：缺少参数。');
        }

        $order = ArrayToolkit::parts($order, array('userId', 'title', 'amount', 'targetType', 'targetId', 'payment', 'note', 'snPrefix', 'data'));

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

    public function payOrder($payData)
    {
        $order = $this->getOrderDao()->getOrderBySn($payData['sn']);
        if (empty($order)) {
            throw $this->createServiceException("订单({$payData['sn']})已被删除，支付失败。");
        }

        if ($payData['status'] == 'success') {
            // 避免浮点数比较大小可能带来的问题，转成整数再比较。
            if (intval($payData['amount']*100) !== intval($order['amount']*100)) {
                $message = sprintf('订单(%s)的金额(%s)与实际支付的金额(%s)不一致，支付失败。', array($order['sn'], $order['price'], $payData['amount']));
                $this->_createLog($order['id'], 'pay_error', $message, $payData);
                throw $this->createServiceException($message);
            }

            if ($this->canOrderPay($order)) {
                $this->getOrderDao()->updateOrder($order['id'], array(
                    'status' => 'paid',
                    'paidTime' => $payData['paidTime'],
                ));
                $this->_createLog($order['id'], 'pay_success', '付款成功', $payData);

            } else {
                $this->_createLog($order['id'], 'pay_ignore', '订单已处理，付款被忽略', $payData);
            }
        } else {
            $this->_createLog($order['id'], 'pay_unknown', '', $payData);
        }

        return $this->getOrder($order['id']);
    }

    public function canOrderPay($order)
    {
        if (empty($order['status'])) {
            throw new \InvalidArgumentException();
        }
        return in_array($order['status'], array('created'));
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