<?php

namespace OpenLivePlugin;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\PluginBundle\System\PluginBase;

class OpenLivePlugin extends PluginBase
{
    public function boot()
    {
        parent::boot();
        $this->initializeBiz($this->container->get('biz'));
    }

    public function initializeBiz(Biz $biz)
    {
        $biz->register(new \OpenLivePlugin\Biz\DefaultServiceProvider());
    }

    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }
}
