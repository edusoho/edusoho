<?php

namespace Biz\Cash\Service\Impl;

use Biz\BaseService;
use Biz\Cash\Dao\CashOrdersDao;
use Biz\Cash\Dao\CashOrdersLogDao;
use Biz\Cash\Service\CashOrdersService;
use Biz\Cash\Service\CashService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;

class CashOrdersServiceImpl extends BaseService implements CashOrdersService
{
    public function getOrder($id)
    {
        return $this->getOrderDao()->get($id);
    }

    public function addOrder($order)
    {
        $coinSetting = $this->getSettingService()->get('coin', array());

        if (!is_numeric($order['amount'])) {
            throw $this->createInvalidArgumentException('充值金额必须为整数!');
        }

        $coin = $coinSetting['cash_rate'] * $order['amount'];
        $order['sn'] = 'O'.date('YmdHis').rand(10000, 99999);
        $order['status'] = 'created';
        $order['title'] = '充值购买'.$coin.$coinSetting['coin_name'];
        $order['createdTime'] = time();
        $order['token'] = $this->makeToken($order['sn']);

        return $this->getOrderDao()->create($order);
    }

    public function getOrderBySn($sn, $lock = false)
    {
        return $this->getOrderDao()->getBySn($sn, array('lock' => $lock));
    }

    public function getOrderByToken($token)
    {
        return $this->getOrderDao()->getByToken($token);
    }

    public function cancelOrder($id, $message = '', $data = array())
    {
        $order = $this->getOrder($id);

        if (empty($order)) {
            throw $this->createNotFoundException('订单不存在，取消订单失败！');
        }

        if (!in_array($order['status'], array('created'))) {
            throw $this->createServiceException('当前订单状态不能取消订单！');
        }

        $order = $this->getOrderDao()->update($order['id'], array('status' => 'cancelled'));

        $this->_createLog($order['id'], 'cancelled', $message, $data);

        return $order;
    }

    public function updateOrder($id, $fields)
    {
        return $this->getOrderDao()->update($id, $fields);
    }

    public function payOrder($payData)
    {
        $success = true;

        try {
            $this->beginTransaction();

            $order = $this->getOrderDao()->getBySn($payData['sn'], array('lock' => true));

            if (empty($order)) {
                throw $this->createNotFoundException(sprintf('订单(%s)已被删除，支付失败。', $payData['sn']));
            }

            if ($payData['status'] == 'success') {
                // 避免浮点数比较大小可能带来的问题，转成整数再比较。

                if (intval($payData['amount'] * 100) !== intval($order['amount'] * 100)) {
                    $message = sprintf('订单(%s)的金额(%s)与实际支付的金额(%s)不一致，支付失败。', $order['sn'], $order['amount'], $payData['amount']);
                    $this->_createLog($order['id'], 'pay_error', $message, $payData);
                    throw $this->createServiceException($message);
                }

                if ($this->canOrderPay($order)) {
                    $this->getOrderDao()->update($order['id'], array(
                        'status' => 'paid',
                        'paidTime' => $payData['paidTime'],
                    ));

                    $this->_createLog($order['id'], 'pay_success', '付款成功', $payData);

                    $userId = $order['userId'];
                    $inFlow = array(
                        'userId' => $userId,
                        'amount' => $order['amount'],
                        'name' => '入账',
                        'orderSn' => $order['sn'],
                        'category' => 'outflow',
                        'note' => '',
                        'payment' => $order['payment'],
                    );

                    $rmbInFlow = $this->getCashService()->inflowByRmb($inFlow);

                    $rmbOutFlow = array(
                        'userId' => $userId,
                        'amount' => $order['amount'],
                        'name' => '出账',
                        'orderSn' => $order['sn'],
                        'category' => 'inflow',
                        'note' => '',
                        'parentSn' => $rmbInFlow['sn'],
                    );

                    $this->getCashService()->changeRmbToCoin($rmbOutFlow);

                    $success = true;
                    $this->dispatchEvent('order.pay.success',
                        new Event($order, array('targetType' => 'coin'))
                    );
                } else {
                    $this->_createLog($order['id'], 'pay_ignore', '订单已处理', $payData);
                }
            } else {
                $this->_createLog($order['id'], 'pay_unknown', '', $payData);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        $order = $this->getOrderDao()->get($order['id']);

        return array($success, $order);
    }

    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
        $this->closeOrders();

        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getOrderDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchOrdersCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getOrderDao()->count($conditions);
    }

    public function closeOrders()
    {
        $time = time() - 48 * 3600;
        $this->getOrderDao()->closeOrders($time);
    }

    public function analysisAmount($conditions)
    {
        return $this->getOrderDao()->analysisAmount($conditions);
    }

    public function createPayRecord($id, array $payData)
    {
        $order = $this->getOrder($id);
        $data = $order['data'];

        if (!is_array($data)) {
            $data = json_decode($order['data'], true);
        }

        foreach ($payData as $key => $value) {
            $data[$key] = $value;
        }

        $fields = array('data' => json_encode($data));
        $order = $this->updateOrder($id, $fields);
        $this->_createLog($order['id'], 'cash_pay_create', '创建交易', $payData);
    }

    protected function _createLog($orderId, $type, $message = '', array $data = array())
    {
        $user = $this->getCurrentUser();

        $log = array(
            'orderId' => $orderId,
            'type' => $type,
            'message' => $message,
            'data' => json_encode($data),
            'userId' => $user['id'],
            'ip' => $user['currentIp'],
            'createdTime' => time(),
        );

        return $this->getOrderLogDao()->create($log);
    }

    public function getLogsByOrderId($orderId)
    {
        return $this->getOrderLogDao()->findByOrderId($orderId);
    }

    public function canOrderPay($order)
    {
        if (empty($order['status'])) {
            throw new \InvalidArgumentException();
        }

        return in_array($order['status'], array('created'));
    }

    protected function _prepareSearchConditions($conditions)
    {
        if (isset($conditions['mobile'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($conditions['mobile']);
            $conditions['userId'] = $user ? $user['id'] : -1;
        }
        if (isset($conditions['email'])) {
            $user = $this->getUserService()->getUserByEmail($conditions['email']);
            $conditions['userId'] = $user ? $user['id'] : -1;
        }

        return $conditions;
    }

    private function makeToken($sn)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $value = '';
        for ($i = 0; $i < 5; ++$i) {
            $value .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $sn.$value;
    }

    /**
     * @return CashOrdersDao
     */
    protected function getOrderDao()
    {
        return $this->createDao('Cash:CashOrdersDao');
    }

    /**
     * @return CashOrdersLogDao
     */
    protected function getOrderLogDao()
    {
        return $this->createDao('Cash:CashOrdersLogDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CashService
     */
    protected function getCashService()
    {
        return $this->createService('Cash:CashService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
