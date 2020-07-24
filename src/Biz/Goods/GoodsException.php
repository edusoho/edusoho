<?php

namespace Biz\Goods;

use AppBundle\Common\Exception\AbstractException;

class GoodsException extends AbstractException
{
    const EXCEPTION_MODULE = 72;

    const GOODS_NOT_FOUND = 4047201;

    const SPECS_NOT_FOUND = 4027202;

    public $messages = [
        self::GOODS_NOT_FOUND => 'exception.goods.not_found',
        self::SPECS_NOT_FOUND => 'exception.specs.not_found',
    ];
}
