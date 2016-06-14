<?php

namespace Permission\PermissionBundle;

use Topxia\Common\ExtensionalBundle;

class PermissionBundle extends ExtensionalBundle
{
	public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }
}
