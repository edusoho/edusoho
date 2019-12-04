<?php

namespace Biz\NewComer;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Service\AppService;

class PluginRegisterTask extends BaseNewcomer
{
    public function getStatus()
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());

        if (!empty($newcomerTask['plugin_register_task']['status'])) {
            return true;
        }

        $apps = $this->getAppService()->findApps(0, $this->getAppService()->findAppCount());
        $appTypes = ArrayToolkit::column($apps, 'type');
        if (in_array(AppService::PLUGIN_TYPE, $appTypes)) {
            $this->doneTask('plugin_register_task');

            return true;
        }

        return false;
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }
}
