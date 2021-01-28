<?php

namespace Biz\WeChat;

use AppBundle\Common\Exception\AbstractException;

class WeChatException extends AbstractException
{
    const EXCEPTION_MODULE = 66;

    const NOTIFY_SETTING_NOT_ENABLED = '4036602';

    const TEMPLATE_EXCEEDS_LIMIT = '4036601';

    const TEMPLATE_CONFLICT_INDUSTRY = '4036603';

    const TOKEN_MAKE_ERROR = '5006601';

    const TEMPLATE_OPEN_ERROR = '5006602';

    public $messages = array(
        4036601 => 'exception.wechat.template_exceeds_limit_hint',
        4036602 => 'exception.wechat.notify_setting_not_enabled',
        4036603 => 'exception.wechat.template_conflict_industry_hint',
        5006601 => 'wechat.notification.empty_token',
        5006602 => 'wechat.notification.template_open_error',
    );
}
