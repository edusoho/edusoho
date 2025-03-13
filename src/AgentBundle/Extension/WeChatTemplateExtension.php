<?php

namespace MarketingMallBundle\Extension;

use AppBundle\Extension\Extension;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Common\WechatNotification\MessageSubscriberTemplateUtil;
use MarketingMallBundle\Common\WechatNotification\MessageTemplateUtil;

class WeChatTemplateExtension extends Extension
{
    public function getWeChatTemplates()
    {
        return $this->getMallService()->isInit() ? MessageTemplateUtil::templates() : [];
    }

    public function getMessageSubscribeTemplates()
    {
        return $this->getMallService()->isInit() ? MessageSubscriberTemplateUtil::templates() : [];
    }

    public function register()
    {
    }

    /**
     * @return MallService
     */
    protected function getMallService()
    {
        return $this->biz->service('Mall:MallService');
    }
}
