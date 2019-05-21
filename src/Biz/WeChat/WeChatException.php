<?php

namespace Biz\WeChat;

use AppBundle\Common\Exception\AbstractException;

class WeChatException extends AbstractException
{
    const EXCEPTION_MODULE = 66;

    public $messages = array(
        4046601 => 'exception.wechat.',
    );
}
