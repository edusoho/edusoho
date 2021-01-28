<?php

namespace ApiBundle\Api\Resource\Crontab;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\System;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\SettingService;
use Biz\User\UserException;

class Crontab extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }

        $crontabStatus = array(
            'enabled' => false,
        );

        if (System::getOS() === System::OS_WIN) {
            $setting = $this->getSettingService()->get('magic', array());
            $crontabStatus['status'] = !empty($setting['disable_web_crontab']);
        }

        if (System::getOS() === System::OS_OSX || System::OS_LINUX) {
            $crontabJobs = SystemCrontabInitializer::findCrontabJobs();

            $crontabStatus['enabled'] = count($crontabJobs) > 0;
        }

        return $crontabStatus;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        $this->service('System:SettingService');
    }
}
