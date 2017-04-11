<?php

namespace Biz\Coupon\Type;

use Codeages\Biz\Framework\Context\BizAware;

abstract class BaseCoupon extends BizAware
{
    /**
     * @param $coupon
     * @param array $target 例: {id: 1, type: course}
     *
     * @return bool
     */
    abstract public function canUseable($coupon, $target);
}
