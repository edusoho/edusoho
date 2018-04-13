<?php

namespace AppBundle\Common\Exception;

class UserException 
{
    public $moduleCode = 01;

    const UN_LOGIN = 01;

    public $messages = array(
        01 => array(
            'statusCode' => 403,
            'message' => '用户未登录'
        ),
        02 => array(
            'statusCode' => 404,
            'message' => '限制登录'
        ), 
    );
}
