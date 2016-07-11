<?php
namespace Topxia\Service\Coupon\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Coupon\CouponService;

class CouponServiceImpl extends BaseService implements CouponService
{
    public function getCoupon($id)
    {
        return $this->getCouponDao()->getCoupon($id);
    }

    public function getCouponsByIds($ids)
    {
        return $this->getCouponDao()->getCouponsByIds($ids);
    }

    public function addCoupon($coupon)
    {
        return $this->getCouponDao()->addCoupon($coupon);
    }

    public function updateCoupon($couponId, $fields)
    {
        return $this->getCouponDao()->updateCoupon($couponId, $fields);
    }

    public function findCouponsByBatchId($batchId, $start, $limit)
    {
        $coupons = $this->getCouponDao()->findCouponsByBatchId($batchId, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function findCouponsByIds(array $ids)
    {
        $coupons = $this->getCouponDao()->findCouponsByIds($ids);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCoupons(array $conditions, $orderBy, $start, $limit)
    {
        $coupons = $this->getCouponDao()->searchCoupons($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCouponsCount(array $conditions)
    {
        return $this->getCouponDao()->searchCouponsCount($conditions);
    }

    public function generateInviteCoupon($userId, $mode) //user可能是邀请者*pay，也可能是被邀请者*register

    {
        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (!in_array($mode, array('register', 'pay'))) {
            return array();
        }

        switch ($mode) {
            case 'register':
                $settingName = 'promoted_user_value';
                $rewardName  = '注册';
                break;

            case 'pay':
                $settingName = 'promote_user_value';
                $rewardName  = '邀请';
                break;
        }

        if (isset($inviteSetting['invite_code_setting'])
            && $inviteSetting['invite_code_setting'] == 1
            && $inviteSetting[$settingName] > 0) {
            $couponCode     = $this->generateRandomCode(10, 'inviteCoupon');
            $isCouponUnique = $this->isCouponUnique($couponCode);

            if ($isCouponUnique) {
                $inviteSetting = $this->getSettingService()->get('invite', array());
                $coupon        = array(
                    'code'        => $couponCode,
                    'type'        => 'minus',
                    'status'      => 'receive',
                    'rate'        => $inviteSetting[$settingName],
                    'userId'      => $userId,
                    'batchId'     => null,
                    'deadline'    => strtotime(date('Y-m-d')) + $inviteSetting['deadline'] * 24 * 3600,
                    'targetType'  => 'all',
                    'targetId'    => 0,
                    'createdTime' => time()
                );

                $coupon = $this->getCouponDao()->addCoupon($coupon);

                $card = $this->getCardService()->addCard(array(
                    'cardId'      => $coupon['id'],
                    'cardType'    => 'coupon',
                    'status'      => 'receive',
                    'deadline'    => $coupon['deadline'],
                    'useTime'     => 0,
                    'userId'      => $coupon['userId'],
                    'createdTime' => time()
                ));
                $message = array(
                    'rewardName'  => $rewardName,
                    'settingName' => $inviteSetting[$settingName]
                );
                $this->getNotificationService()->notify($userId, 'invite-reward', $message);
                return $coupon;
            } else {
                return $this->generateInviteCoupon($userId, $mode);
            }
        }

        return array();
    }

    protected function isCouponUnique($couponCode)
    {
        $haveCoupon = $this->getCouponByCode($couponCode);

        if ($haveCoupon) {
            return false;
        } else {
            return true;
        }
    }

    public function deleteCouponsByBatch($batchId)
    {
        return $this->getCouponDao()->deleteCouponsByBatch($batchId);
    }

    public function checkCouponUseable($code, $targetType, $targetId, $amount)
    {
        $coupon      = $this->getCouponByCode($code);
        $currentUser = $this->getCurrentUser();

        if (empty($coupon)) {
            return array(
                'useable' => 'no',
                'message' => '优惠券'.$code.'不存在'
            );
        }

        if ($coupon['status'] != 'unused' && $coupon['status'] != 'receive') {
            return array(
                'useable' => 'no',
                'message' => '优惠券'.$code.'已经被使用'
            );
        }

        if ($coupon['userId'] != 0 && $coupon['userId'] != $currentUser['id']) {
            return array(
                'useable' => 'no',
                'message' => '优惠券'.$code.'已经被其他人领取'
            );
        }

        if ($coupon['deadline'] + 86400 < time()) {
            return array(
                'useable' => 'no',
                'message' => '优惠券'.$code.'已过期'
            );
        }

        if ($targetType != $coupon['targetType'] && $coupon['targetType'] != 'all' && $coupon['targetType'] != 'fullDiscount') {
            return array(
                'useable' => 'no',
                'message' => '优惠券'.$code.'不可用'
            );
        }

        if ($coupon['targetId'] != 0 && $targetId != $coupon['targetId']) {
            return array(
                'useable' => 'no',
                'message' => '优惠券'.$code.'不可用'
            );
        }

        if ($coupon['targetType'] == 'fullDiscount') {
            if ($amount < $coupon['fullDiscountPrice']) {
                return array(
                    'useable' => 'no',
                    'message' => '优惠券'.$code.'不可用'
                );
            }
        }

        if ($coupon['type'] == 'minus') {
            $coin = $this->getSettingService()->get("coin");

            if (isset($coin["coin_enabled"]) && isset($coin["price_type"]) && $coin["coin_enabled"] == 1 && $coin["price_type"] == "Coin") {
                $discount = $coupon['rate'] * $coin["cash_rate"];
            } else {
                $discount = $coupon['rate'];
            }

            $afterAmount = $amount - $discount;
        }

        if ($coupon['status'] == 'unused') {
            $coupon = $this->getCouponDao()->updateCoupon($coupon['id'], array(
                'userId' => $currentUser['id'],
                'status' => 'receive'
            ));
            $this->getLogService()->info('coupon', 'receive', "用户{$currentUser['nickname']}(#{$currentUser['id']})领取了优惠码 {$coupon['code']}", $coupon);
            if (empty($coupon)) {
                return false;
            }
        }

        if ($coupon['type'] == 'discount') {
            $afterAmount = $amount * $coupon['rate'] / 10;
        }

        $afterAmount = $afterAmount < 0 ? 0.00 : $afterAmount;

        $afterAmount    = number_format($afterAmount, 2, '.', '');
        $decreaseAmount = $amount - $afterAmount;

        return array(
            'useable'        => 'yes',
            'afterAmount'    => $afterAmount,
            'decreaseAmount' => $decreaseAmount,
            'type'           => $coupon['type'],
            'rate'           => $coupon['rate']
        );
    }

    public function getCouponByCode($code)
    {
        return $this->getCouponDao()->getCouponByCode($code);
    }

    public function useCoupon($code, $order)
    {
        $coupon = $this->getCouponDao()->getCouponByCode($code, true);
        $user   = $this->getUserService()->getUser($order['userId']);
        if (empty($coupon)) {
            return null;
        }

        if ($coupon['status'] == 'used') {
            return null;
        }

        $coupon = $this->getCouponDao()->updateCoupon($coupon['id'], array(
            'status'     => 'used',
            'targetType' => $order['targetType'],
            'targetId'   => $order['targetId'],
            'orderTime'  => time(),
            'userId'     => $order['userId'],
            'orderId'    => $order['id']
        ));
        $this->getLogService()->info('coupon', 'use', "用户{$user['nickname']}(#{$user['id']})使用了优惠码 {$coupon['code']}", $coupon);
        $this->dispatchEvent('coupon.use', $coupon);

        return $coupon;
    }

    private function generateRandomCode($length, $prefix)
    {
        $randomCode = "";

        for ($j = 0; $j < (int) $length; $j++) {
            $randomCode .= mt_rand(0, 9);
        }

        $randomCode = $prefix.$randomCode;

        return $randomCode;
    }

    private function getCouponBatchService()
    {
        return $this->createService('Coupon:Coupon.CouponBatchService');
    }

    private function getTokenService()
    {
        return $this->createService('User.TokenService');
    }

    private function getNotifiactionService()
    {
        return $this->createService('User.NotificationService');
    }

    private function getInviteRecordService()
    {
        return $this->createService('User.InviteRecordService');
    }

    private function getOrderService()
    {
        return $this->createService('Order.OrderService');
    }

    private function getCardService()
    {
        return $this->createService('Card.CardService');
    }

    private function getCouponDao()
    {
        return $this->createDao('Coupon.CouponDao');
    }

    private function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getNotificationService()
    {
        return $this->createService('User.NotificationService');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
