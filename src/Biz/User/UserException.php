<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class UserException extends AbstractException
{
    const MODUAL = 01;

    const UN_LOGIN = 4040101;

    const LIMIT_LOGIN = 4030102;

    public $messages = array(
        4040101 => 'exception.user.unlogin',
        4030102 => 'exception.user.unlogin',
    );
}
