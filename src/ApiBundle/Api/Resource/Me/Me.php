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
        $this->appendUser($user);

        return $user;
    }

    private function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->service('VipPlugin:Vip:VipService')->getMemberByUserId($user['id']);
            $level = $this->service('VipPlugin:Vip:LevelService')->getLevel($vip['levelId']);
            if ($vip) {
                $user['vip'] = array(
                    'levelId' => $vip['levelId'],
                    'vipName' => $level['name'],
                    'deadline' => date('c', $vip['deadline']),
                    'seq' => $level['seq'],
                );
            } else {
                $user['vip'] = null;
            }
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
}
