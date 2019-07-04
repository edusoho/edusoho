<?php

namespace Biz\Coupon\State;

use Biz\System\Service\LogService;
use Biz\User\Service\UserService;
use AppBundle\Common\Exception\AccessDeniedException;

class UsingCoupon extends Coupon implements CouponInterface
{
    public function using()
    {
        throw new AccessDeniedException('Can not using coupon which status is using!');
    }

    public function used($params)
    {
        $coupon = $this->getCouponService()->updateCoupon(
            $this->coupon['id'],
            array(
                'status' => 'used',
                'targetType' => $params['targetType'],
                'targetId' => $params['targetId'],
                'orderTime' => time(),
                'userId' => $params['userId'],
                'orderId' => $params['orderId'],
            )
        );

        $card = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

        if (!empty($card)) {
            $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', array(
                'status' => 'used',
                'useTime' => $coupon['orderTime'],
            ));
        }

        $this->dispatchEvent('coupon.use', $coupon);

        $this->getLogService()->info(
            'coupon',
            'use',
            "使用了优惠券 {$coupon['code']}",
            $coupon
        );
    }

    public function cancelUsing()
    {
        $coupon = $this->getCouponService()->updateCoupon($this->coupon['id'], array(
            'status' => 'receive',
        ));

        $card = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

        if (!empty($card)) {
            $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', array(
                'status' => 'receive',
            ));
        }
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->biz->service('CloudPlatform:AppService');
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->biz->service('CouponPlugin:Coupon:CouponBatchService');
    }
}
