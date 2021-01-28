<?php

namespace Biz\Coupon\State;

use AppBundle\Common\Exception\AccessDeniedException;

class ReceiveCoupon extends Coupon implements CouponInterface
{
    public function using()
    {
        $coupon = $this->getCouponService()->updateCoupon(
            $this->coupon['id'],
            array(
                'status' => 'using',
            )
        );
        $card = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

        //优惠卡临时被占用卡包视为已使用状态
        if (!empty($card)) {
            $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', array(
                'status' => 'used',
            ));
        }
    }

    public function used($params)
    {
        throw new AccessDeniedException('Can not directly used coupon which status is unused!');
    }

    public function cancelUsing()
    {
        throw new AccessDeniedException('Can not cancel using coupon which status is unused!');
    }
}
