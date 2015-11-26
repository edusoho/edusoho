<?php

namespace Topxia\WebBundle;

use Topxia\Common\ExtensionalBundle;

class TopxiaWebBundle extends ExtensionalBundle
{
    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }

}
