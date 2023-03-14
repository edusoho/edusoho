<?php

namespace MarketingMallBundle\Extension;

use AppBundle\Extension\Extension;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class WeChatTemplateExtension extends Extension
{
    public function getWeChatTemplates()
    {
        return MessageTemplateUtil::templates();
    }

    public function getMessageSubscribeTemplates()
    {
        return MessageSubscriberTemplateUtil::templates();
    }

    public function register()
    {
    }
}
