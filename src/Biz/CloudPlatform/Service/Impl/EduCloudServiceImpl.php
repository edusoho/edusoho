<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\CloudPlatform\Service\EduCloudService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Biz\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Biz\CloudPlatform\CloudAPIFactory;

class EduCloudServiceImpl extends BaseService implements EduCloudService
{
    public function isHiddenCloud()
    {
        try {
            $api = CloudAPIFactory::create('root');
            $overview = $api->get("/cloud/{$api->getAccessKey()}/overview");
        } catch (\RuntimeException $e) {
            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));
            $logger->addInfo($e->getMessage());

            return false;
        }
        if (!isset($overview['error'])) {
            return $overview['accessCloud'] && $overview['enabled'];
        }

        return false;
    }

    public function getOldSmsUserStatus()
    {
        $smsAccount = $this->getSettingService()->get('sms_account');
        if ($this->isReloadSmsAccountFromCloud($smsAccount)) {
            $smsAccount = $this->getUserSmsAccountFromCloud();
        }
        if ($smsAccount['status'] == 'unusual') {
            return $smsAccount;
        }

        return false;
    }

    private function isReloadSmsAccountFromCloud($smsAccount)
    {
        if (!$smsAccount) {
            return true;
        }
        if ($smsAccount['status'] != 'normal' && $smsAccount['checkTime'] < time()) {
            return true;
        }
    }

    private function getUserSmsAccountFromCloud()
    {
        /*
        * @accessCloud->false: 没有云平台帐号或者未接入教育云
        */
        try {
            $api = CloudAPIFactory::create('root');
            $smsAccount = $api->get('/me/sms_account');
        } catch (\RuntimeException $e) {
            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));
            $logger->addInfo($e->getMessage());

            $smsAccount = array('status' => 'uncheck', 'checkTime' => time() + 60 * 10, 'isOldSmsUser' => 'unknown');
            $this->getSettingService()->set('sms_account', $smsAccount);

            return $smsAccount;
        }
        $smsAccountStatus = isset($smsAccount['status']) && 'used' == $smsAccount['status'];
        $accessCloud = isset($smsAccount['accessCloud']) && false == $smsAccount['accessCloud'];
        if ($smsAccountStatus && $accessCloud) {
            $smsInfo['remainCount'] = $smsAccount['remainCount'];

            $smsAccount = array('status' => 'unusual', 'checkTime' => time() + 60 * 60 * 24, 'isOldSmsUser' => true, 'remainCount' => $smsAccount['remainCount']);
            $this->getSettingService()->set('sms_account', $smsAccount);

            return $smsAccount;
        }
        $smsAccount = array('status' => 'normal');
        $this->getSettingService()->set('sms_account', $smsAccount);

        return $smsAccount;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
