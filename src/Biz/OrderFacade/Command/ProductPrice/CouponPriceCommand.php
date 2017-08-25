<?php

namespace Biz\OrderFacade\Command\ProductPrice;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class CouponPriceCommand extends Command
{
    public function execute(Product $product)
    {
        if (!empty($product->pickedDeducts['coupon'])) {
            $couponInfo = $product->pickedDeducts['coupon'];
            if (empty($couponInfo)) {
                return;
            }

            $checkData = $this->getCouponService()->checkCoupon($couponInfo['code'], $product->targetId, $product->targetType);

            if (isset($checkData['useable']) && $checkData['useable'] == 'no') {
                throw new InvalidArgumentException('Bad coupon code use');
            }

            $coupon = $this->getCouponService()->getCouponByCode($couponInfo['code']);

            if ($coupon['type'] == 'minus') {
                $coupon['deduct_amount'] = $coupon['rate'];
            } else {
                $coupon['deduct_amount'] = round($product->price * ($coupon['rate'] / 10), 2);
            }

            $product->pickedDeducts['coupon']['deduct_amount'] = $coupon['deduct_amount'];
            $product->pickedDeducts['coupon']['id'] = $coupon['id'];
            $product->payablePrice -= $coupon['deduct_amount'];
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
