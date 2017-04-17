<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class Me extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->service('User:UserService')->getUser($this->getCurrentUser()->getId());
        $profile = $this->service('User:UserService')->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
        return $user;
    }
}