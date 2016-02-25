<?php
namespace Topxia\Service\Sms\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Sms\SmsService;
use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

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
            throw new \RuntimeException("云短信相关设置未开启!");
        }

        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        $to      = implode(',', $mobiles);

        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->post("/sms/send", array('mobile' => $to, 'category' => $smsType, 'description' => $description, 'parameters' => $parameters));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException("发送失败！");
        }

        $users        = $this->getUserService()->findUsersByIds($userIds);
        $userProfiles = ArrayToolkit::index($this->getUserService()->findUserProfilesByIds($userIds), 'id');

        $message = '对';

        foreach ($users as $user) {
            $message .= 'userId: '.$user['id'].';nickname: '.$user['nickname'].';verifiedMobile: '.$user['verifiedMobile'].';mobile: '.$userProfiles[$user['id']]['mobile'].';';
        }

        $message .= '发送用于'.$smsType.'的通知短信';
        $this->getLogService()->info('sms', $smsType, $message, $mobiles);

        return true;
    }

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
