<?php

namespace MarketingMallBundle\Api\Resource\MallUser;

use ApiBundle\Api\Annotation\AuthClass;
use ApiBundle\Api\ApiRequest;
use Biz\User\Service\UserService;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallUserResetPassword extends BaseResource
{
    /**
     * @AuthClass(ClassName="MarketingMallBundle\Security\Firewall\MallAuthTokenAuthenticationListener")
     */
    public function add(ApiRequest $request, $id)
    {
        $password = $request->request->all();
        $this->getUserService()->initPassword($id, $password['password']);

        return ['success' => true];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
