<?php
namespace Topxia\Service\CloudPlatform\Impl;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\EduCloudService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class EduCloudServiceImpl extends BaseService implements EduCloudService
{
    public function isHiddenCloud()
    {
        try {
            $api  = CloudAPIFactory::create('root');
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
        /*
         * @accessCloud->false: 没有云平台帐号或者未接入教育云
         */
        try {
            $api  = CloudAPIFactory::create('root');
            $smsAccount  = $api->get("/me/sms_account");
        } catch (\RuntimeException $e) {
            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));
            $logger->addInfo($e->getMessage());
            return false;
        }
        $smsAccountStatus = isset($smsAccount['status']) && 'used' == $smsAccount['status'];
        $accessCloud = isset($smsAccount['accessCloud']) && false == $smsAccount['accessCloud'];
        if ($smsAccountStatus && $accessCloud) {
            $smsInfo['remainCount'] = $smsAccount['remainCount'];
            return $smsInfo;
        }
        return false;
    }
}