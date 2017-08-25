<?php

namespace Biz\OrderFacade\Command;

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

            $checkData = $this->getCouponService()->checkCouponUseable($couponInfo['code'], $product->targetType, $product->targetId, $product->payablePrice);

            if ($checkData['useable'] !== 'yes') {
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