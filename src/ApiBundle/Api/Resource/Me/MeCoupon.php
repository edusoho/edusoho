<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ResponseFilter;
use Biz\Coupon\CouponException;
use Biz\Common\CommonException;

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
     * @ResponseFilter(class="ApiBundle\Api\Resource\Coupon\CouponFilter", mode="public")
     */
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $token = $request->request->get('token');
        if (empty($token)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $result = $this->getCouponBatchService()->receiveCoupon($token, $user['id']);

        if ('success' != $result['code']) {
            if (isset($result['exception'])) {
                $exceptionMethod = $result['exception']['method'];
                throw $result['exception']['class']::$exceptionMethod();
            } else {
                throw CouponException::RECEIVE_FAILED();
            }
        }

        $coupon = $this->getCouponService()->getCoupon($result['id']);
        $coupon['target'] = $this->getCouponBatchService()->getTargetByBatchId($coupon['batchId']);
        $coupon['targetDetail'] = $this->getCouponBatchService()->getCouponBatchTargetDetail($coupon['batchId']);

        return $coupon;
    }

    private function getCouponBatchService()
    {
        return $this->service('Coupon:CouponBatchService');
    }

    private function getCouponService()
    {
        return $this->service('Coupon:CouponService');
    }

    private function getCardService()
    {
        return $this->service('Card:CardService');
    }
}
