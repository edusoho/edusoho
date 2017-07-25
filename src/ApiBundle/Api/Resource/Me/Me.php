<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\RewardPoint\Service\AccountService;
use Biz\User\Service\UserService;

class Me extends AbstractResource
{
    public function get(ApiRequest $request)
    {
        $user = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);
        $user['rewardPointAccount'] = $this->getRewardPointAccountService()->getAccountByUserId($user['id']);
        return $user;
    }

    /**
     * @return AccountService
     */
    private function getRewardPointAccountService()
    {
        return $this->service('RewardPoint:AccountService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}