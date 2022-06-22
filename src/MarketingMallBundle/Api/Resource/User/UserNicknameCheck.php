<?php


namespace MarketingMallBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class UserNicknameCheck extends AbstractResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function search(ApiRequest $request, $nickname)
    {
        $user = $this->getUserService()->getUserByNickname(urldecode($nickname));

        return [
            'is_exist' => empty($user) ? false : true,
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
