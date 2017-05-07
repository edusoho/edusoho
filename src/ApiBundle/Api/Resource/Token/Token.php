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

        $args = array(
            'userId' => $user['id'],
            'os' => $this->getOs($request)
        );

        $token = $this->getTokenService()->makeApiAuthToken($args);

        $this->appendUser($user);

        return array(
            'token' => $token['token'],
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

    private function getOs(ApiRequest $request)
    {
        $detector = new DeviceDetectorAdapter($request->headers->get('User-Agent'));
        $os = $detector->getOs();
        return $os ? $os['name'] : null;
    }

    /**
     * @return TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}