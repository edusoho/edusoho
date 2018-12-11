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
        }

        $product->availableDeducts['coupon'] = $this->availableCouponsSort($availableCoupons, $product);
    }

    /**
     * 对可用的优惠券进行排序
     * 1.专属优惠券排在前面
     * 2.面额从大到小
     * 3.到期日期从小至大
     * 4.创建日期从小至大
     *
     * @return array
     */
    private function availableCouponsSort($availableCoupons = array(), $product)
    {
        if ('course' == $product->targetType) {
            $course = $this->getCourseService()->getCourse($product->targetId);
            $targetId = $course['courseSetId'];
        } else {
            $targetId = $product->targetId;
        }

        $exclusiveCoupons = array();
        $unexclusiveCoupons = array();

        foreach ($availableCoupons as $availableCoupon) {
            if ($availableCoupon['targetType'] == $product->targetType && $availableCoupon['targetId'] == $targetId) {
                $exclusiveCoupons[] = $availableCoupon;
            } else {
                $unexclusiveCoupons[] = $availableCoupon;
            }
        }

        usort($exclusiveCoupons, array($this, 'compareCoupon'));

        usort($unexclusiveCoupons, array($this, 'compareCoupon'));

        return array_merge($exclusiveCoupons, $unexclusiveCoupons);
    }

    private function compareCoupon($coupon1, $coupon2)
    {
        if ($coupon1['deduct_amount'] == $coupon2['deduct_amount']) {
            if ($coupon1['deadline'] == $coupon2['deadline']) {
                return $coupon1['createdTime'] > $coupon2['createdTime'];
            } else {
                return $coupon1['deadline'] > $coupon2['deadline'];
            }
        } else {
            return $coupon1['deduct_amount'] < $coupon2['deduct_amount'];
        }
    }

    private function availableCouponsByIdAndType($id, $type)
    {
        if ('course' == $type) {
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
