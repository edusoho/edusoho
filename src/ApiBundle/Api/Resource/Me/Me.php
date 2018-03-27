<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\User\Service\UserService;
use VipPlugin\Biz\Vip\Service\VipService;

class Me extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
        $user['vip'] = null;
        if ($this->isPluginInstalled('vip')) {
            $apiRequest = new ApiRequest('/api/plugins/vip/vip_users/'.$user['id'], 'GET');
            $user['vip'] = $this->invokeResource($apiRequest);
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

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }
}
