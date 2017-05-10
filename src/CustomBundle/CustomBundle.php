<?php

namespace CustomBundle;

use Codeages\PluginBundle\System\PluginBase;

class CustomBundle extends PluginBase
{

    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }
}
