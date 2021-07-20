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

        return [
            'isBindMobile' => empty($user['verifiedMobile']) ? false : true,
            'mobile_bind_mode' => $this->getSettingService()->get('auth.mobile_bind_mode', 'constraint')
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