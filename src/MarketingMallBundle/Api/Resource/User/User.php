<?php

namespace MarketingMallBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;

class User extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $request = $request->query;
        $ids = explode(',',$request->get("userIds"));
        return $this->getUserService()->findUpdateUserProfilesByIds($ids);
    }

    /**
     * @return UserService
     */
    protected function getUserService()

    {
        return $this->service('User:UserService');
    }
}
