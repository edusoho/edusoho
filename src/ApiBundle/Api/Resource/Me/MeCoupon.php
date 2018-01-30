<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Card\Service\CardService;
use Biz\Coupon\Service\CouponService;

class MeCoupon extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = array(
            'userId' => $this->getCurrentUser()->getId(),
            'status' => 'receive',
            'cardType' => 'coupon',
        );

        $myCards = $this->getCardService()->searchCards(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return array_values($this->getCouponService()->findCouponsByIds(array_column($myCards, 'cardId')));
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }

    /**
     * @return CardService
     */
    private function getCardService()
    {
        return $this->service('Card:CardService');
    }
}
