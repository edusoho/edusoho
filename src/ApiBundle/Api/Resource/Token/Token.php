<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\LogService;
use Biz\User\Service\UserService;

class Token extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $type = $request->request->get('type');
        $user = $this->getCurrentUser()->toArray();

        $token = $this->getUserService()->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30);

        $this->appendUser($user);
        $this->getUserService()->markLoginInfo($type);

        return array(
            'token' => $token,
            'user' => $user,
        );
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

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }
}
