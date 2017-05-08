<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
use AppBundle\Component\DeviceDetector\DeviceDetectorAdapter;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;

class Token extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser()->toArray();

        $token = $this->getUserService()->makeToken('mobile_login', $user['id'], time() + 3600 * 24 * 30);

        $this->appendUser($user);

        return array(
            'token' => $token,
            'user' => $user
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
                    'seq' => $level['seq']
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