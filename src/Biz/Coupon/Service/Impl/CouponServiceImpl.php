<?php

namespace Biz\Coupon\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Card\Service\CardService;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use Biz\User\Service\UserService;

class CouponServiceImpl extends BaseService implements CouponService
{
    public function getCoupon($id)
    {
        return $this->getCouponDao()->get($id);
    }

    public function getCouponsByIds($ids)
    {
        return $this->getCouponDao()->findByIds($ids);
    }

    public function addCoupon($coupon)
    {
        return $this->getCouponDao()->create($coupon);
    }

    public function updateCoupon($couponId, $fields)
    {
        return $this->getCouponDao()->update($couponId, $fields);
    }

    public function findCouponsByBatchId($batchId, $start, $limit)
    {
        $coupons = $this->getCouponDao()->findByBatchId($batchId, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function findCouponsByIds(array $ids)
    {
        $coupons = $this->getCouponDao()->findByIds($ids);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCoupons(array $conditions, $orderBy, $start, $limit)
    {
        $coupons = $this->getCouponDao()->search($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($coupons, 'id');
    }

    public function searchCouponsCount(array $conditions)
    {
        return $this->getCouponDao()->count($conditions);
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
                $rewardName = '注册';
                break;

            case 'pay':
                $settingName = 'promote_user_value';
                $rewardName = '邀请';
                break;
        }

        if (isset($inviteSetting['invite_code_setting'])
            && $inviteSetting['invite_code_setting'] == 1
            && $inviteSetting[$settingName] > 0
        ) {
            $couponCode = $this->generateRandomCode(10, 'inviteCoupon');
            $isCouponUnique = $this->isCouponUnique($couponCode);

            if ($isCouponUnique) {
                $inviteSetting = $this->getSettingService()->get('invite', array());
                $coupon = array(
                    'code' => $couponCode,
                    'type' => 'minus',
                    'status' => 'receive',
                    'rate' => $inviteSetting[$settingName],
                    'userId' => $userId,
                    'batchId' => null,
                    'deadline' => strtotime(date('Y-m-d')) + $inviteSetting['deadline'] * 24 * 3600,
                    'targetType' => 'all',
                    'targetId' => 0,
                    'createdTime' => time(),
                );

                $coupon = $this->getCouponDao()->create($coupon);

                $card = $this->getCardService()->addCard(
                    array(
                        'cardId' => $coupon['id'],
                        'cardType' => 'coupon',
                        'status' => 'receive',
                        'deadline' => $coupon['deadline'],
                        'useTime' => 0,
                        'userId' => $coupon['userId'],
                        'createdTime' => time(),
                    )
                );
                $message = array(
                    'rewardName' => $rewardName,
                    'settingName' => $inviteSetting[$settingName],
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
        return $this->getCouponDao()->deleteByBatch($batchId);
    }

    public function checkCouponUseable($code, $targetType, $targetId, $amount)
    {
        $coupon = $this->getCouponByCode($code);
        $currentUser = $this->getCurrentUser();

        if (empty($coupon)) {
            return array(
                'useable' => 'no',
                'message' => '该优惠券不存在',
            );
        }

        if ($coupon['status'] != 'unused' && $coupon['status'] != 'receive') {
            return array(
                'useable' => 'no',
                'message' => sprintf('优惠券%s已经被使用', $code),
            );
        }

        if ($coupon['userId'] != 0 && $coupon['userId'] != $currentUser['id']) {
            return array(
                'useable' => 'no',
                'message' => sprintf('优惠券%s已经被其他人领取使用', $code),
            );
        }

        if ($coupon['deadline'] + 86400 < time()) {
            return array(
                'useable' => 'no',
                'message' => sprintf('优惠券%s已过期', $code),
            );
        }

        if ($targetType != $coupon['targetType'] && $coupon['targetType'] != 'all' && $coupon['targetType'] != 'fullDiscount') {
            return array(
                'useable' => 'no',
                'message' => '',
            );
        }

        if ($coupon['targetType'] == 'fullDiscount' and $amount < $coupon['fullDiscountPrice']) {
            return array(
                'useable' => 'no',
                'message' => '',
            );
        }

        if ($coupon['type'] == 'minus') {
            $coin = $this->getSettingService()->get('coin');

            if (isset($coin['coin_enabled']) && isset($coin['price_type']) && $coin['coin_enabled'] == 1 && $coin['price_type'] == 'Coin') {
                $discount = $coupon['rate'] * $coin['cash_rate'];
            } else {
                $discount = $coupon['rate'];
            }

            $afterAmount = $amount - $discount;
        }

        if ($coupon['targetId'] != 0) {
            $couponFactory = $this->biz['coupon_factory'];
            $couponModel = $couponFactory($coupon['targetType']);
            if (!$couponModel->canUseable($coupon, array('id' => $targetId, 'type' => $targetType))) {
                return array(
                    'useable' => 'no',
                    'message' => '',
                );
            }
        }

        if ($coupon['status'] == 'unused') {
            $coupon = $this->getCouponDao()->update(
                $coupon['id'],
                array(
                    'userId' => $currentUser['id'],
                    'status' => 'receive',
                )
            );
            $this->getLogService()->info(
                'coupon',
                'receive',
                "用户{$currentUser['nickname']}(#{$currentUser['id']})领取了优惠券 {$coupon['code']}",
                $coupon
            );
            if (empty($coupon)) {
                return false;
            }
        }

        if ($coupon['type'] == 'discount') {
            $afterAmount = $amount * $coupon['rate'] / 10;
        }

        $afterAmount = $afterAmount < 0 ? 0.00 : $afterAmount;

        $afterAmount = number_format($afterAmount, 2, '.', '');
        $decreaseAmount = $amount - $afterAmount;

        return array(
            'useable' => 'yes',
            'afterAmount' => $afterAmount,
            'decreaseAmount' => $decreaseAmount,
            'type' => $coupon['type'],
            'rate' => $coupon['rate'],
        );
    }

    public function getCouponByCode($code)
    {
        return $this->getCouponDao()->getByCode($code);
    }

    public function useCoupon($code, $order)
    {
        $coupon = $this->getCouponDao()->getByCode($code, array('lock' => 1));
        $user = $this->getUserService()->getUser($order['userId']);
        if (empty($coupon)) {
            return null;
        }

        if ($coupon['status'] == 'used') {
            return null;
        }

        $coupon = $this->getCouponDao()->update(
            $coupon['id'],
            array(
                'status' => 'used',
                'targetType' => $order['targetType'],
                'targetId' => $order['targetId'],
                'orderTime' => time(),
                'userId' => $order['userId'],
                'orderId' => $order['id'],
            )
        );
        $this->getLogService()->info(
            'coupon',
            'use',
            "用户{$user['nickname']}(#{$user['id']})使用了优惠券 {$coupon['code']}",
            $coupon
        );
        $this->dispatchEvent('coupon.use', $coupon);

        return $coupon;
    }

    private function generateRandomCode($length, $prefix)
    {
        $randomCode = '';

        for ($j = 0; $j < (int) $length; ++$j) {
            $randomCode .= mt_rand(0, 9);
        }

        $randomCode = $prefix.$randomCode;

        return $randomCode;
    }

    /**
     * @return CardService
     */
    private function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    /**
     * @return CouponDao
     */
    private function getCouponDao()
    {
        return $this->createDao('Coupon:CouponDao');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
