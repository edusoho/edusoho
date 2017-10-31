<?php

namespace Biz\Coupon\State;

use Biz\Card\Service\CardService;
use Biz\Coupon\Service\CouponService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Event\Event;

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

    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }
}
