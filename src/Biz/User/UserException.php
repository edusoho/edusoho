<?php

namespace AppBundle\Common\Exception;

class UserException 
{
    const UN_LOGIN = '404_USER_01';

    const LIMIT_LOGIN = '403_USER_02';

    public $messages = array(
        '404_USER_01' => '用户未登录',
        '403_USER_02' => '限制登录',
    );
}
