<?php


namespace ApiBundle\Api\Resource\Token;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;

class TokenRefresh extends AbstractResource
{
    public function add(ApiRequest $request, $refreshToken)
    {
        $user = $this->getCurrentUser()->toArray();
        $client = $request->request->get('client', '');
        $token = $request->headers->get("X-Auth-Token","");
        //根据refreshtoken查询用户token//更新token
        $userToken = $this -> getUserService() -> updateToken($token, $user['id'], $refreshToken, time() + 3600 * 24 * 30);
        if (empty($userToken)) {
            return [];
        }
        //返回token和过期时间
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