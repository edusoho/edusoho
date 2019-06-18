<?php

namespace Biz\WeChat;

use AppBundle\Common\Exception\AbstractException;

class WeChatException extends AbstractException
{
    const EXCEPTION_MODULE = 66;

    const NOTIFY_SETTING_NOT_ENABLED = '4036602';

    public $messages = array(
        4046601 => 'exception.wechat.',
        4036602 => 'exception.wechat.notify_setting_not_enabled',
    );
}
