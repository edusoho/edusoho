<?php
namespace Custom\Service\Order\Impl;

use Custom\Service\Order\Order1Service;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Order\Impl\OrderServiceImpl;
use Topxia\Common\ArrayToolkit;

class Order1ServiceImpl extends OrderServiceImpl implements Order1Service
{
    public function createOrder($order)
    {   
        $user=$this->getCurrentUser();
        if (!ArrayToolkit::requireds($order, array('userId', 'title',  'amount', 'targetType', 'targetId', 'payment'))) {
            throw $this->createServiceException('创建订单失败：缺少参数。');
        }

        $order = ArrayToolkit::parts($order, array('userId', 'title', 'amount', 'targetType', 'targetId', 'payment', 'note', 'snPrefix', 'data', 'couponCode'));

        $orderUser = $this->getUserService()->getUser($order['userId']);
        if (empty($orderUser)) {
            throw $this->createServiceException("订单用户(#{$order['userId']})不存在，不能创建订单。");
        }

        if (!in_array($order['payment'], array('none', 'alipay', 'alipaydouble', 'tenpay'))) {
            throw $this->createServiceException('创建订单失败：payment取值不正确。');
        }

        $order['sn'] = $this->generateOrderSn($order);
        unset($order['snPrefix']);

        if (!empty($order['couponCode'])){
            $couponInfo = $this->getCouponService()->checkCouponUseable($order['couponCode'], $order['targetType'], $order['targetId'], $order['amount']);
            if ($couponInfo['useable'] != 'yes') {
                throw $this->createServiceException("优惠码不可用");            
            }

            $order['couponDiscount'] = $order['amount'] - $couponInfo['afterAmount'];
            $order['amount'] = $couponInfo['afterAmount'];
        }
        $order['coupon'] = empty($order['couponCode']) ? '' : $order['couponCode'];
        unset($order['couponCode']);

        $order['amount'] = number_format($order['amount'], 2, '.', '');
        if (intval($order['amount']*100) == 0) {
            $order['payment'] = 'none';
        }

        $vip=$this->getVipService()->getMemberByUserId($user->id);
        $level=array();

        if($vip){

            $level=$this->getLevelService()->getLevel($vip['levelId']);

            if($level){
                
                $order['amount']=$order['amount']*0.1*$level['courseDiscount'];

                $order['amount']=sprintf("%.2f", $order['amount']);
            }
        }

        $order['status'] = 'created';
        $order['createdTime'] = time();
        $order = $this->getOrderDao()->addOrder($order);

        if ($order['coupon']) {
            $this->getCouponService()->useCoupon($order['coupon'], $order);
        }

        $this->_createLog($order['id'], 'created', '创建订单');
        return $order;
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
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

    private function _prepareSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);
        
        if (isset($conditions['date'])) {
            $dates = array(
                'yesterday'=>array(
                    strtotime('yesterday'),
                    strtotime('today'),
                ),
                'today'=>array(
                    strtotime('today'),
                    strtotime('tomorrow'),
                ),
                'this_week' => array(
                    strtotime('Monday this week'),
                    strtotime('Monday next week'),
                ),
                'last_week' => array(
                    strtotime('Monday last week'),
                    strtotime('Monday this week'),
                ),
                'next_week' => array(
                    strtotime('Monday next week'),
                    strtotime('Monday next week', strtotime('Monday next week')),
                ),
                'this_month' => array(
                    strtotime('first day of this month midnight'), 
                    strtotime('first day of next month midnight'),
                ),
                'last_month' => array(
                    strtotime('first day of last month midnight'),
                    strtotime('first day of this month midnight'),
                ),
                'next_month' => array(
                    strtotime('first day of next month midnight'),
                    strtotime('first day of next month midnight', strtotime('first day of next month midnight')),
                ),
            );

            if (array_key_exists($conditions['date'], $dates)) {
                $conditions['paidStartTime'] = $dates[$conditions['date']][0];
                $conditions['paidEndTime'] = $dates[$conditions['date']][1];
                unset($conditions['date']);
            }
        }
        
        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
        }
        unset($conditions['keywordType']);
        unset($conditions['keyword']);

        if (isset($conditions['buyer'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['buyer']);
            $conditions['userId'] = $user ? $user['id'] : -1;
        }

        return $conditions;
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getOrderRefundDao()
    {
        return $this->createDao('Order.OrderRefundDao');
    }

    private function getOrderDao()
    {
        return $this->createDao('Order.OrderDao');
    }

    private function getOrderLogDao()
    {
        return $this->createDao('Order.OrderLogDao');
    }

    private function getCouponService()
    {
        return $this->createService('Coupon:Coupon.CouponService');
    }

    protected function getVipService()
    {
        return $this->createService('Vip:Vip.VipService');
    } 

    protected function getLevelService()
    {
        return $this->createService('Vip:Vip.LevelService');
    }
}