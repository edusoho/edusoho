<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\RewardPoint\Service\AccountService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;

class MeRewardPoint extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $rewardPoint = $this->getSettingService()->get('reward_point', array());
        if (empty($rewardPoint) || !$rewardPoint['enable']) {
            return null;
        }

        $currentUser = $this->getCurrentUser();
        $account = $this->getRewardPointAccountService()->getAccountByUserId($currentUser->getId());
        return array(
            'userId' => $currentUser->getId(),
            'balance' => isset($account['balance']) ? $account['balance'] : 0,
            'title' => $rewardPoint['name']
        );
    }

    /**
     * @return AccountService
     */
    private function getRewardPointAccountService()
    {
        return $this->service('RewardPoint:AccountService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}