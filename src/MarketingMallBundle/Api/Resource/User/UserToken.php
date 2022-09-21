<?php


namespace MarketingMallBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class UserToken extends AbstractResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if(!empty($user)) {
            $token = $this->getUserService()->makeToken("mobile_login", $user['id'], time() + 3600 * 24 * 30, ['client' => "marketing_mall"]);

            $this->getUserService()->markLoginInfo('h5');
            return [
                'token' => $token,
            ];
        }

        return [
            'error' => 'not found user:'.$userId,
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
