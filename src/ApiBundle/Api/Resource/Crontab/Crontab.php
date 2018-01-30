<?php

namespace ApiBundle\Api\Resource\Crontab;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\System;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Crontab extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        if (!$this->getCurrentUser()->isAdmin()) {
            throw new AccessDeniedHttpException('无权限访问此接口');
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
