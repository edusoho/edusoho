<?php

namespace AppBundle\Util;

use AppBundle\Common\TimeMachine;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Firebase\JWT\JWT;
use Topxia\Service\Common\ServiceKernel;

class PlayToken
{
    public function make($fileId, $ttl = 60)
    {
        $user = $this->getCurrentUser();
        $metas = "{$user['uuid']}|{$fileId}";
        $payload = [
            'iss' => 'EduSoho',
            'aud' => 'EduSoho',
            'exp' => TimeMachine::time() + $ttl,
            'metas' => $metas,
        ];

        return JWT::encode($payload, $this->getKey());
    }

    public function parse($token)
    {
        if (empty($token)) {
            return null;
        }
        $payload = JWT::decode($token, $this->getKey(), ['HS256']);
        list($uuid, $fileId) = explode('|', $payload->metas);

        $user = $this->getUserService()->getUserByUUID($uuid);
        if (empty($user)) {
            return null;
        }

        return [
            'userId' => $user['id'],
            'fileId' => $fileId,
        ];
    }

    private function getCurrentUser()
    {
        return $this->getBiz()['user'];
    }

    private function getKey()
    {
        $storage = $this->getSettingService()->get('storage', []);
        $accessKey = empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'];
        $secretKey = empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'];

        return md5($accessKey.$secretKey);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    private function getBiz()
    {
        return ServiceKernel::instance()->getBiz();
    }
}
