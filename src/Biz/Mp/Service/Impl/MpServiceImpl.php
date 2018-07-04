<?php

namespace Biz\Mp\Service\Impl;

use Biz\BaseService;
use Biz\Mp\Service\MpService;
use Biz\System\Service\SettingService;

class MpServiceImpl extends BaseService implements MpService
{
    public function getMpSdk()
    {
        return $this->biz['qiQiuYunSdk.mp'];
    }

    public function getAuthorization()
    {
        return $this->getMpSdk()->getAuthorization();
    }

    public function generateInitUrl($params, $schema = 'http')
    {
        return $this->getMpServerUrl($schema).'/es/setup?token='.$this->getToken($params);
    }

    public function generateVersionManagementUrl($params, $schema = 'http')
    {
        return $this->getMpServerUrl($schema).'/es/mini_program?token='.$this->getToken($params);
    }

    protected function getToken($params)
    {
        $token = $this->getMpSdk()->getToken($params);

        return $token['token'];
    }

    protected function getMpServerUrl($schema)
    {
        $developerSetting = $this->getSettingService()->get('developer', array());
        $hostUrl = 'mp-service.qiqiuyun.net';
        if (isset($developerSetting['mp_service_url']) && !empty($developerSetting['mp_service_url'])) {
            $urlSegs = explode('://', $developerSetting['mp_service_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }

        return $schema.'://'.$hostUrl;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
