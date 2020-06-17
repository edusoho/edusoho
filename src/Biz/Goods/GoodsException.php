<?php

namespace Biz\Goods;

use AppBundle\Common\Exception\AbstractException;

class GoodsException extends AbstractException
{
    const EXCEPTION_MODULE = 72;

    const GOODS_NOT_FOUND = 4047201;

    public $messages = [
        4047201 => 'exception.goods.not_found',
    ];
}
