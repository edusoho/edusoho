<?php

namespace Biz\Coupon\Type;

use Codeages\Biz\Framework\Context\Biz;

abstract class BaseCoupon
{
    /**
     * @var $biz
     */
    private $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return Biz
     */
    protected function getBiz()
    {
        return $this->biz;
    }

    /**
     * @param $coupon
     * @param array $target 例: {id: 1, type: course}
     * @return bool
     */
    abstract public function canUseable($coupon, $target);
}
