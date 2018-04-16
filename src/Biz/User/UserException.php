<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class UserException extends AbstractException
{
    const UN_LOGIN = 4040101;

    const LIMIT_LOGIN = 4030102;

    public $messages = array(
        4040101 => '用户未登录',
        4030102 => '限制登录',
    );
}