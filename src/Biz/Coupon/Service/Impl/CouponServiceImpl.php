<?php

namespace Biz\Coupon\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Card\Service\CardService;
use Biz\Coupon\CouponException;
use Biz\Coupon\Dao\CouponDao;
use Biz\Coupon\Service\CouponService;
use Biz\Coupon\State\ReceiveCoupon;
use Biz\Coupon\State\UsingCoupon;
use Biz\Course\Service\CourseService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\NotificationService;
use CouponPlugin\Biz\Coupon\Service\CouponBatchService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use VipPlugin\Biz\Vip\Service\LevelService;

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
        $coupon = $this->getCouponDao()->update($couponId, $fields);
        $this->dispatchEvent('coupon.update', $coupon);

        return $coupon;
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

    /**
     * [generateInviteCoupon description]
     *
     * @param [int]    $userId [description]
     * @param [string] $mode   user可能是邀请者*pay，也可能是被邀请者*register
     *
     * @return [type] [description]
     */
    public function generateInviteCoupon($userId, $mode)
    {
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

        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (isset($inviteSetting['invite_code_setting'])
            && 1 == $inviteSetting['invite_code_setting']
            && $inviteSetting[$settingName] > 0
        ) {
            $coupon = $this->generateCoupon(
                array(
                    'type' => 'inviteCoupon',
                    'userId' => $userId,
                    'price' => $inviteSetting[$settingName],
                    'expireDayCount' => $inviteSetting['deadline'],
                )
            );
            $message = array(
                'rewardName' => $rewardName,
                'settingName' => $inviteSetting[$settingName],
            );
            $this->getNotificationService()->notify($userId, 'invite-reward', $message);
            $this->dispatchEvent('invite.reward', $coupon, array('message' => $message));

            return $coupon;
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

    //接口有问题  fullDiscount 没用到
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

        if ('unused' != $coupon['status'] && 'receive' != $coupon['status']) {
            return array(
                'useable' => 'no',
                'message' => sprintf('优惠券%s已经被使用', $code),
            );
        }

        if (0 != $coupon['userId'] && $coupon['userId'] != $currentUser['id']) {
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

        if ($targetType != $coupon['targetType'] && 'all' != $coupon['targetType'] && 'fullDiscount' != $coupon['targetType']) {
            return array(
                'useable' => 'no',
                'message' => '',
            );
        }

//        if ($coupon['targetType'] == 'fullDiscount' and $amount < $coupon['fullDiscountPrice']) {
//            return array(
//                'useable' => 'no',
//                'message' => '',
//            );
//        }

        if ('minus' == $coupon['type']) {
            $coin = $this->getSettingService()->get('coin');

            if (isset($coin['coin_enabled']) && isset($coin['price_type']) && 1 == $coin['coin_enabled'] && 'Coin' == $coin['price_type']) {
                $discount = $coupon['rate'] * $coin['cash_rate'];
            } else {
                $discount = $coupon['rate'];
            }

            $afterAmount = $amount - $discount;
        }

        if (0 != $coupon['targetId']) {
            $couponFactory = $this->biz['coupon_factory'];
            $couponModel = $couponFactory($coupon['targetType']);
            if (!$couponModel->canUseable($coupon, array('id' => $targetId, 'type' => $targetType))) {
                return array(
                    'useable' => 'no',
                    'message' => '',
                );
            }
        }

        if ('unused' == $coupon['status']) {
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

        if ('discount' == $coupon['type']) {
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

    public function checkCoupon($code, $id, $type)
    {
        try {
            $this->beginTransaction();
            $coupon = $this->getCouponByCode($code, true);
            $currentUser = $this->getCurrentUser();
            //todo 国际化
            $message = array(
                'useable' => 'no',
            );

            $factory = $this->biz['ratelimiter.factory'];
            $limiter = $factory('coupon_check', 60, 3600);

            if (0 == $limiter->getAllow($currentUser->getId())) {
                $message['message'] = '优惠码校验受限，请稍后尝试';
                $this->commit();

                return $message;
            }

            if (empty($coupon)) {
                $message['message'] = '该优惠券不存在';
            }

            if (empty($message['message']) && 'unused' != $coupon['status'] && 'receive' != $coupon['status']) {
                $message['message'] = sprintf('优惠券%s已经被使用', $code);
            }

            if (empty($message['message']) && 0 != $coupon['userId'] && $coupon['userId'] != $currentUser['id']) {
                $message['message'] = sprintf('优惠券%s已经被其他人领取使用', $code);
            }

            if (empty($message['message']) && ($coupon['deadline'] + 86400 < time())) {
                $message['message'] = sprintf('优惠券%s已过期', $code);
            }

            if (empty($message['message']) && $this->isAvailableForTarget($coupon, $type, $id)) {
                $message['message'] = '该优惠券不能被该商品使用';
            }

            if (!empty($message['message'])) {
                $remain = $limiter->check($currentUser->getId());
                $this->commit();

                return $message;
            }

            if ('unused' == $coupon['status']) {
                $this->receiveCouponByUserId($coupon['id'], $currentUser['id']);
            }
            $this->commit();

            return $coupon;
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogService()->error('coupon', 'checkCoupon', "优惠码校验失败code: {$code}", array('message' => $e->getMessage()));

            return array(
                'useable' => 'no',
                'message' => '异常情况，优惠码不能被使用',
            );
        }
    }

    private function isAvailableForTarget($coupon, $targetType, $targetId)
    {
        if ('course' == $targetType) {
            $course = $this->getCourseService()->getCourse($targetId);
            $targetId = $course ? $course['courseSetId'] : null;
        }

        return !('all' == $coupon['targetType'] || ($coupon['targetType'] == $targetType && ($coupon['targetId'] == $targetId || 0 == $coupon['targetId'])));
    }

    public function getCouponByCode($code, $lock = false)
    {
        return $this->getCouponDao()->getByCode($code, array('lock' => $lock));
    }

    private function receiveCouponByUserId($couponId, $useId)
    {
        $coupon = $this->getCouponDao()->update(
            $couponId,
            array(
                'userId' => $useId,
                'status' => 'receive',
            )
        );

        if ($this->isPluginInstalled('Coupon')) {
            $this->getCouponBatchService()->updateUnreceivedNumByBatchId($coupon['batchId']);
        }

        $this->getCardService()->addCard(array(
            'cardType' => 'coupon',
            'cardId' => $coupon['id'],
            'deadline' => $coupon['deadline'],
            'userId' => $useId,
        ));

        $this->getLogService()->info(
            'coupon',
            'receive',
            "用户(#{$useId})领取了优惠券 (#{$couponId})",
            $coupon
        );
    }

    private function generateRandomCode($length, $prefix)
    {
        $randomCode = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);

        $code = substr($randomCode, 0, $length);
        $code = $prefix.strtoupper($code);

        $isUnique = $this->isCouponUnique($code);
        if ($isUnique) {
            return $code;
        } else {
            return $this->generateRandomCode($length, $prefix);
        }
    }

    public function getDeductAmount($coupon, $price)
    {
        if ('minus' == $coupon['type']) {
            return $coupon['rate'];
        } else {
            return $price > 0 ? round($price * ((10 - $coupon['rate']) / 10), 2) : 0;
        }
    }

    public function getCouponStateById($couponId)
    {
        $coupon = $this->getCoupon($couponId);

        if (!$coupon) {
            $this->createNewException(CouponException::NOTFOUND_COUPON());
        }

        switch ($coupon['status']) {
            case 'using':
                return new UsingCoupon($this->biz, $coupon);
            case 'receive':
                return new ReceiveCoupon($this->biz, $coupon);
            default:
                $this->createNewException(CouponException::STATUS_INVALID());
                break;
        }
    }

    public function generateDistributionCoupon($userId, $rate, $expireDay)
    {
        $coupon = $this->generateCoupon(
            array(
                'type' => 'distributionCoupon',
                'userId' => $userId,
                'price' => $rate,
                'expireDayCount' => $expireDay,
            )
        );

        $this->getNotificationService()->notify($userId, 'distributor-reward', $coupon);

        return $coupon;
    }

    public function generateMarketingCoupon($userId, $rate, $expireDay)
    {
        $coupon = $this->generateCoupon(
            array(
                'type' => 'marketingCoupon',
                'userId' => $userId,
                'price' => $rate / 100,
                'expireDayCount' => $expireDay,
            )
        );

        return $coupon;
    }

    public function getCouponTargetByTargetTypeAndTargetId($targetType, $targetId)
    {
        $target = null;
        if (empty($targetType) || empty($targetId)) {
            return $target;
        }
        switch ($targetType) {
            case 'course':
                $target = $this->getCourseSetService()->getCourseSet($targetId);
                break;

            case 'vip':
                if ($this->isPluginInstalled('Vip')) {
                    $target = $this->getLevelService()->getLevel($targetId);
                }
                break;

            case 'classroom':
                $target = $this->getClassroomService()->getClassroom($targetId);
                break;

            default:
                break;
        }

        return $target;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return LevelService
     */
    private function getLevelService()
    {
        return $this->biz->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CouponBatchService
     */
    protected function getCouponBatchService()
    {
        return $this->createService('CouponPlugin:Coupon:CouponBatchService');
    }

    protected function isPluginInstalled($code)
    {
        $app = $this->getAppService()->getAppByCode($code);

        return !empty($app);
    }

    protected function getAppService()
    {
        return $this->biz->service('CloudPlatform:AppService');
    }

    /**
     * @param $info
     * array(
     *  'type' => 'distributionCoupon',
     *  'userId' => 1,
     *  'price' => 100.01,  // 优惠券金额
     *  'expireDayCount' => 1, // 过期时间，单位为天
     * )
     */
    protected function generateCoupon($info)
    {
        $couponCode = $this->generateRandomCode(10, $info['type']);

        $coupon = array(
            'code' => $couponCode,
            'type' => 'minus',
            'status' => 'receive',
            'rate' => intval($info['price']),
            'userId' => $info['userId'],
            'batchId' => null,
            'deadline' => strtotime(date('Y-m-d')) + intval($info['expireDayCount']) * 24 * 3600,
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

        return $coupon;
    }
}
