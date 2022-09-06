<?php


namespace ApiBundle\Api\Resource\Token;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;

class TokenRefresh extends AbstractResource
{
    public function add(ApiRequest $request, $refreshToken)
    {
        $token = $request->headers->get("X-Auth-Token","");
        $userToken = $this -> getUserService() -> refreshToken($token, $refreshToken, time() + 3600 * 24 * 30);
        if (empty($userToken)) {
            return [];
        }

        return[
            'token' => $userToken['token'],
            'tokenExpire' => $userToken['expiredTime'],
        ];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}