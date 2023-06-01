<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

interface NotificationEvent
{
    public function getServiceFollowTemplateKey();

    public function getMessageSubscribeTemplateKey();

    public function getSmsTemplateKey();

    public function buildServiceFollowTemplateArgs($data);

    public function buildMessageSubscribeTemplateArgs($data);

    public function buildSmsTemplateArgs($data);

    public function getToUserIds($data);

    public function getOpenIdMap($data);

    public function getSubscribeStatus($data);

    public function getGotoUrl($data);
}
