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

        $request = $request->request->all();
        unset($request['user']['id']);
        return $this->getUserService()->syncBindUser($request);

    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}