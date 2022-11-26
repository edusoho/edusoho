<?php


namespace MarketingMallBundle\Api\Resource\WxLogin;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use MarketingMallBundle\Api\Resource\BaseResource;

class WxLogin extends BaseResource
{
    public function add(ApiRequest $request)
    {

        $fromId = $request->request->all();
        return $this->getUserService()->syncBindUser($fromId['fromId']);

    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}