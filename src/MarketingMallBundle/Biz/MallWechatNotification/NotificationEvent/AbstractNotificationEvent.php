<?php

namespace MarketingMallBundle\Biz\MallWechatNotification\NotificationEvent;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractNotificationEvent
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }
}
