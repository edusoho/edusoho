<?php

namespace MarketingMallBundle\Api\Resource\MallUser;

use ApiBundle\Api\ApiRequest;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use MarketingMallBundle\Api\Resource\BaseResource;

class MallUserResetPassword extends BaseResource {
    public function add(ApiRequest $request, $id) {
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
