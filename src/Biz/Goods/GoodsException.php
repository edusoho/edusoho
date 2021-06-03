<?php

namespace Biz\Goods;

use AppBundle\Common\Exception\AbstractException;

class GoodsException extends AbstractException
{
    const EXCEPTION_MODULE = 72;

    const GOODS_NOT_FOUND = 4047201;

    const SPECS_NOT_FOUND = 4047202;

    const FORBIDDEN_MANAGE_GOODS = 4037203;

    const FORBIDDEN_JOIN_UNPUBLISHED_GOODS = 4037204;

    const FORBIDDEN_JOIN_UNPUBLISHED_SPECS = 4037205;

    public $messages = [
        self::GOODS_NOT_FOUND => 'exception.goods.not_found',
        self::SPECS_NOT_FOUND => 'exception.specs.not_found',
        self::FORBIDDEN_MANAGE_GOODS => 'exception.goods.forbidden_manage',
        self::FORBIDDEN_JOIN_UNPUBLISHED_GOODS => 'exception.goods.forbidden_join_unpublished_goods',
        self::FORBIDDEN_JOIN_UNPUBLISHED_SPECS => 'exception.goods.forbidden_join_unpublished_specs',
    ];
}
