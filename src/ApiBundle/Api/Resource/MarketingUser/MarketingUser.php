<?php

namespace ApiBundle\Api\Resource\MarketingUser;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\AuthService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;

class MarketingUser extends AbstractResource
{
    /**
     * @param ApiRequest $request
     *
     * @return mixed
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     * @ResponseFilter(class="ApiBundle\Api\Resource\User\UserFilter", mode="simple")
     */
    public function add(ApiRequest $request)
    {
        $postData = $request->request->all();

        $logger = $this->biz['logger'];
        $token = $this->getTokenService()->makeToken(
            'marketing',
            array(
                'data' => array(
                    'type' => 'marketing',
                ),
                'times' => 1,
                'duration' => 3600,
                'userId' => $postData['user_id'],
            )
        );

        $registration['token'] = $token;
        $registration['verifiedMobile'] = $postData['mobile'];
        $registration['mobile'] = $postData['mobile'];
        $registration['nickname'] = $postData['nickname'];
        $logger->info('Marketing用户名：'.$registration['nickname']);
        $registration['nickname'] = $this->getUserService()->generateNickname($registration);
        $logger->info('ES用户名：'.$registration['nickname']);
        $registration['registeredWay'] = 'web';
        $registration['createdIp'] = isset($postData['client_ip']) ? $postData['client_ip'] : '';
        $registration['password'] = $postData['password'];
        $registration['type'] = 'marketing';

        $user = $this->getAuthService()->register($registration, 'marketing');

        return $user;
    }

    /**
     * @return TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return AuthService
     */
    private function getAuthService()
    {
        return $this->service('User:AuthService');
    }
}
