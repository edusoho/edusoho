<?php

namespace Org\OrgBundle;

use Topxia\Common\ExtensionalBundle;

class OrgBundle extends ExtensionalBundle
{
    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }

}
