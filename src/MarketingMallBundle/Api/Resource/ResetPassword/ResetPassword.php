<?php

namespace MarketingMallBundle\Api\Resource\ResetPassword;

use ApiBundle\Api\ApiRequest;
use Biz\User\Service\UserService;
use MarketingMallBundle\Api\Resource\BaseResource;

class ResetPassword extends BaseResource {
    public function add(ApiRequest $request) {
        $password = $request->request->all();
        $this->getUserService()->initPassword($this->getCurrentUser()->getId(), $password['password']);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
