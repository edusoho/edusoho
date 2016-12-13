<?php
namespace Topxia\Service\Sms\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Sms\SmsService;
use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\User\UserService;

class SmsServiceImpl extends BaseService implements SmsService
{
    public function isOpen($smsType)
    {
        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');

        if ((isset($cloudSmsSetting['sms_enabled'])) && ($cloudSmsSetting['sms_enabled'] == '1')
            && (isset($cloudSmsSetting[$smsType])) && ($cloudSmsSetting[$smsType] == 'on')) {
            return true;
        }

        return false;
    }

    public function smsSend($smsType, $userIds, $description, $parameters = array())
    {
        if (!$this->isOpen($smsType)) {
            throw new \RuntimeException($this->getKernel()->trans('云短信相关设置未开启!'));
        }

        //这里不考虑去重，只考虑unlock
        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        $to      = implode(',', $mobiles);
        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->post("/sms/send", array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'parameters' => $parameters));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException($this->getKernel()->trans('发送失败！'));
        }

        $message = $this->getKernel()->trans('对%To%发送用于%Type%的通知短信',array('%To%'=>$to ,'%Type%'=>$smsType));
        $this->getLogService()->info('sms', $smsType, $message, $mobiles);

        return true;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
