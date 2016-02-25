<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class BaseProcessor {

	protected function getUsersMobile($userIds)
    {
        $to = '';
        if (!empty($userIds)) {
            $users = $this->getUserService()->findUsersByIds($userIds);
            $to = '';
            foreach ($users as $key => $value ) {
                if ($value['locked']) {
                    unset($users[$key]);
                }
            }
            if ($users) {
                $verifiedMobiles = ArrayToolkit::column($users, 'verifiedMobile');

                $userIds = ArrayToolkit::column($users,'id');
                $userProfile = $this->getUserService()->findUserProfilesByIds($userIds);
                $profileMobile = ArrayToolkit::column($userProfile,'mobile');

                $mobile = array_merge($verifiedMobiles,$profileMobile);

                $mobile = array_unique($mobile);
                $to = implode(',', $mobile);
            }
        }
        return $to;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

}