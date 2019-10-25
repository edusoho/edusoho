<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;

class PickCouponCommand extends Command
{
    public function execute(Product $product, $params = array())
    {
        if (!empty($params['couponCode'])) {
            $couponCode = trim($params['couponCode']);
            $checkData = $this->getCouponService()->checkCoupon($couponCode, $product->targetId, $product->targetType);

            if (isset($checkData['useable']) && 'no' == $checkData['useable']) {
                return;
            }

            $coupon = $this->getCouponService()->getCouponByCode($couponCode);

            if ($product->promotionPrice) {
                $coupon['deduct_amount'] = $this->getCouponService()->getDeductAmount($coupon, $product->promotionPrice);
            } else {
                $coupon['deduct_amount'] = $this->getCouponService()->getDeductAmount($coupon, $product->originPrice);
            }

            $deduct = array(
                'deduct_amount' => $coupon['deduct_amount'],
                'deduct_type' => 'coupon',
                'deduct_id' => $coupon['id'],
                'snapshot' => array(
                    'couponCode' => $coupon['code'],
                ),
            );
            $product->pickedDeducts[] = $deduct;
        }
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }
}
