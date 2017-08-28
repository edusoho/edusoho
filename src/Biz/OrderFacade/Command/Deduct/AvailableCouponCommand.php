<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\Card\Service\CardService;
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
                if ($coupon['type'] == 'minus') {
                    $coupon['deduct_amount'] = $coupon['rate'];
                } else {
                    $coupon['deduct_amount'] = round($product->price * ((10 - $coupon['rate']) / 10), 2);
                }
            }

            usort($availableCoupons, function ($coupon1, $coupon2) {
                return $coupon1['deduct_amount'] > $coupon2['deduct_amount'];
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
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
