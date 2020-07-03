<?php

namespace Biz\Favorite;

use AppBundle\Common\Exception\AbstractException;

class FavoriteException extends AbstractException
{
    const EXCEPTION_MODULE = 74;

    const FORBIDDEN_OPERATE_FAVORITE = 4037401;

    public $messages = [
        4037401 => 'exception.favorite.forbidden_operate_favorite',
    ];
}
