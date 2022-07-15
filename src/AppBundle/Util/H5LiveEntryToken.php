<?php

namespace AppBundle\Util;

use AppBundle\Common\TimeMachine;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Firebase\JWT\JWT;
use Topxia\Service\Common\ServiceKernel;

class H5LiveEntryToken
{
    public function make($courseId, $activityId, $replayId = 0, $ttl = 60)
    {
        $user = $this->getCurrentUser();
        $metas = "{$user['uuid']}|{$courseId}|{$activityId}|{$replayId}";
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
        list($uuid, $courseId, $activityId, $replayId) = explode('|', $payload->metas);

        $user = $this->getUserService()->getUserByUUID($uuid);
        if (empty($user)) {
            return null;
        }

        return [
            'userId' => $user['id'],
            'courseId' => $courseId,
            'activityId' => $activityId,
            'replayId' => $replayId,
        ];
    }

    private function getCurrentUser()
    {
        return $this->getServiceKernel()->getCurrentUser();
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
        return $this->getServiceKernel()->getBiz()->service('System:SettingService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
