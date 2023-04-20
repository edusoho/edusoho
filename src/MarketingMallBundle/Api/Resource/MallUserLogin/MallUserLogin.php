<?php

namespace MarketingMallBundle\Api\Resource\MallUserLogin;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use Biz\User\UserException;

class MallUserLogin extends AbstractResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        $user = $this->getUserService()->getUserByLoginField($params['account']);
        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }
        if (!$this->getUserService()->verifyPassword($user['id'], $params['password'])) {
            throw UserException::PASSWORD_ERROR();
        }
        if ($user['locked']) {
            throw UserException::LOCKED_USER();
        }

        return $user;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
