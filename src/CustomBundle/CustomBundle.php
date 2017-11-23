<?php

namespace CustomBundle;

use Codeages\PluginBundle\System\PluginBase;
use CustomBundle\Biz\CustomServiceProvider;

class CustomBundle extends PluginBase
{
    public function getParent()
    {
        return 'AppBundle';
    }

    public function boot()
    {
        parent::boot();
        $biz = $this->container->get('biz');
        $biz->register(new CustomServiceProvider());
    }

    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }
}
