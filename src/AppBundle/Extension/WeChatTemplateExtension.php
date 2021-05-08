<?php

namespace AppBundle\Extension;

use AppBundle\Component\Notification\WeChatTemplateMessage\MessageSubscribeTemplateUtil;
use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;

class WeChatTemplateExtension extends Extension
{
    public function getWeChatTemplates()
    {
        return TemplateUtil::templates();
    }

    public function getMessageSubscribeTemplates()
    {
        return MessageSubscribeTemplateUtil::templates();
    }

    public function register()
    {
    }
}
