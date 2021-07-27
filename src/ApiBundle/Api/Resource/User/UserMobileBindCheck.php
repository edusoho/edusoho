<?php


namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class UserMobileBindCheck extends AbstractResource
{
    public function search(ApiRequest $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)){
            throw UserException::NOTFOUND_USER();
        }

        if ('1' != $this->getSettingService()->node('cloud_sms.sms_enabled') || 'on' != $this->getSettingService()->node("cloud_sms.sms_bind")) {
            $bindMode = 'closed';
        }else{
            $bindMode = $this->getSettingService()->node('login_bind.mobile_bind_mode', 'constraint');
        }

        return [
            'is_bind_mobile' => empty($user['verifiedMobile']) ? false : true,
            'mobile_bind_mode' => $bindMode,
        ];
    }

    /**
     * @return UserService
     */
    protected function getUserService()

    {
        return $this->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
