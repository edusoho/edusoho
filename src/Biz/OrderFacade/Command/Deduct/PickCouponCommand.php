<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class PickCouponCommand extends Command
{
    public function execute(Product $product, $params = array())
    {
        if (!empty($params['couponCode'])) {

            $checkData = $this->getCouponService()->checkCoupon($params['couponCode'], $product->targetId, $product->targetType);

            if (isset($checkData['useable']) && $checkData['useable'] == 'no') {
                throw new InvalidArgumentException('Bad coupon code use');
            }

            $coupon = $this->getCouponService()->getCouponByCode($params['couponCode']);

            if ($coupon['type'] == 'minus') {
                $coupon['deduct_amount'] = $coupon['rate'];
            } else {
                $coupon['deduct_amount'] = round($product->price * ($coupon['rate'] / 10), 2);
            }

            $product->pickedDeducts[]['deduct_amount'] = $coupon['deduct_amount'];
            $product->pickedDeducts[]['deduct_type'] = 'coupon';
            $product->pickedDeducts[]['deduct_id'] = $coupon['id'];
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
