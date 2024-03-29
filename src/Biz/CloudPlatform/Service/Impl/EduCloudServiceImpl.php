<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\Common\JsonLogger;
use Biz\System\Service\CacheService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;
use Biz\Util\EdusohoLiveClient;

class EduCloudServiceImpl extends BaseService implements EduCloudService
{
    private $cloudApi;

    private $isVisible;

    public function isVisibleCloud()
    {
        if (isset($this->isVisible)) {
            return $this->isVisible;
        }

        $this->isVisible = false;
        try {
            $overview = json_decode($this->getCacheService()->get('cloud_status'), true);
            if (empty($overview) || isset($overview['error'])) {
                $api = $this->createCloudApi();
                $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
                $this->getCacheService()->set('cloud_status', json_encode($overview), time() + 3600);
            }
        } catch (\RuntimeException $e) {
            $this->writeErrorLog($e);

            return $this->isVisible;
        }

        if (!isset($overview['error'])) {
            $this->isVisible = $overview['accessCloud'] && $overview['enabled'];
        }

        return $this->isVisible;
    }

    public function getOldSmsUserStatus()
    {
        $smsAccount = $this->getSettingService()->get('sms_account');
        if ($this->isReloadSmsAccountFromCloud($smsAccount)) {
            $smsAccount = $this->getUserSmsAccountFromCloud();
        }
        if ('unusual' == $smsAccount['status']) {
            return $smsAccount;
        }

        return false;
    }

    public function uploadCallbackUrl()
    {
        try {
            $site = $this->getSettingService()->get('site', []);
            if (empty($site['url'])) {
                return 1;
            }
            $client = new EdusohoLiveClient();
            $client->uploadCallbackUrl(rtrim($site['url'], '/').'/callback/live/handle');
        } catch (\RuntimeException $e) {
        }

        return 1;
    }

    public function getLevel()
    {
        $level = $this->getCacheService()->get('site_level');
        if (empty($level)) {
            $me = $this->createCloudApi()->get('/me');
            $level = $me['level'] ?? '';
            $this->getCacheService()->set('site_level', $level, time() + 7200);
        }

        return $level;
    }

    public function isSaaS()
    {
        return in_array($this->getLevel(), $this->getSaasLevels());
    }

    protected function getSaasLevels()
    {
        return ['personal', 'basic', 'medium', 'advanced', 'gold', 'es-basic', 'es-standard', 'es-professional', 'es-flagship'];
    }

    protected function writeErrorLog($e)
    {
        $stream = new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG);
        $logger = new JsonLogger('CloudAPI', $stream);
        $logger->addInfo($e->getMessage());
    }

    private function isReloadSmsAccountFromCloud($smsAccount)
    {
        if (!$smsAccount || ('normal' != $smsAccount['status'] && $smsAccount['checkTime'] < time())) {
            return true;
        }

        return false;
    }

    private function getUserSmsAccountFromCloud()
    {
        /*
        * @accessCloud->false: 没有云平台帐号或者未接入教育云
        */
        try {
            $api = $this->createCloudApi();
            $smsAccount = $api->get('/me/sms_account');
        } catch (\RuntimeException $e) {
            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));
            $logger->addInfo($e->getMessage());

            $smsAccount = ['status' => 'uncheck', 'checkTime' => time() + 60 * 10, 'isOldSmsUser' => 'unknown'];
            $this->getSettingService()->set('sms_account', $smsAccount);

            return $smsAccount;
        }
        $smsAccountStatus = isset($smsAccount['status']) && 'used' == $smsAccount['status'];
        $accessCloud = isset($smsAccount['accessCloud']) && false == $smsAccount['accessCloud'];
        if ($smsAccountStatus && $accessCloud) {
            $smsAccount = ['status' => 'unusual', 'checkTime' => time() + 60 * 60 * 24, 'isOldSmsUser' => true, 'remainCount' => $smsAccount['remainCount']];
            $this->getSettingService()->set('sms_account', $smsAccount);

            return $smsAccount;
        }
        $smsAccount = ['status' => 'normal'];
        $this->getSettingService()->set('sms_account', $smsAccount);

        return $smsAccount;
    }

    protected function createCloudApi()
    {
        if (!$this->cloudApi) {
            $this->cloudApi = CloudAPIFactory::create('root');
        }

        return $this->cloudApi;
    }

    /**
     * 仅给单元测试Mock用
     *
     * @param [type] $api [description]
     */
    public function setCloudApi($api)
    {
        $this->cloudApi = $api;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}
