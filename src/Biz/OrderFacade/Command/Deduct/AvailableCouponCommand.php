<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\Card\Service\CardService;
use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;

class AvailableCouponCommand extends Command
{
    public function execute(Product $product, $params = array())
    {
        $availableCoupons = $this->availableCouponsByIdAndType($product->targetId, $product->targetType);

        if ($availableCoupons) {
            foreach ($availableCoupons as $key => &$coupon) {
                if ($product->promotionPrice) {
                    $coupon['deduct_amount'] = $this->getCouponService()->getDeductAmount($coupon, $product->promotionPrice);
                } else {
                    $coupon['deduct_amount'] = $this->getCouponService()->getDeductAmount($coupon, $product->originPrice);
                }
            }

            usort($availableCoupons, function ($coupon1, $coupon2) {
                return $coupon1['deadline'] > $coupon2['deadline'];
            });
        }

        $product->availableDeducts['coupon'] = $availableCoupons;
    }

    private function availableCouponsByIdAndType($id, $type)
    {
        if ($type == 'course') {
            $course = $this->getCourseService()->getCourse($id);
            $id = $course['courseSetId'];
        }

        return $this->getCardService()->findCurrentUserAvailableCouponForTargetTypeAndTargetId(
            $type, $id
        );
    }

    /**
     * @return CardService
     */
    private function getCardService()
    {
        return $this->biz->service('Card:CardService');
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->biz->service('Coupon:CouponService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
