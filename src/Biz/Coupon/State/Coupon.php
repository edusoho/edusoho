<?php

namespace Biz\Coupon\State;

use Biz\Card\Service\CardService;
use Biz\Coupon\Service\CouponService;
use Codeages\Biz\Framework\Context\Biz;

abstract class Coupon
{
    protected $biz;
    protected $coupon;

    public function __construct(Biz $biz, $coupon)
    {
        $this->biz = $biz;
        $this->coupon = $coupon;
    }

    /***
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->biz->service('Card:CardService');
    }
}