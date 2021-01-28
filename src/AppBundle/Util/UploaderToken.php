<?php

namespace AppBundle\Util;

use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Firebase\JWT\JWT;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\TimeMachine;

/**
 * 素材库文件上传Token.
 */
class UploaderToken
{
    public function make($targetType, $targetId, $bucket, $ttl = 86400)
    {
        $user = $this->getCurrentUser();
        $metas = "{$user['uuid']}|{$targetType}|{$targetId}|{$bucket}";
        $payload = array(
            'iss' => 'EduSoho',
            'aud' => 'EduSoho',
            'exp' => TimeMachine::time() + $ttl,
            'metas' => $metas,
        );

        return JWT::encode($payload, $this->getKey(), 'HS256');
    }

    public function parse($token)
    {
        if (empty($token)) {
            return null;
        }
        $payload = JWT::decode($token, $this->getKey(), array('HS256'));
        $metas = $payload->metas;
        list($uuid, $targetType, $targetId, $bucket) = explode('|', $metas);

        $user = $this->getUserService()->getUserByUUID($uuid);
        if (empty($user)) {
            return null;
        }

        return array(
            'userId' => $user['id'],
            'targetType' => $targetType,
            'targetId' => $targetId,
            'bucket' => $bucket,
        );
    }

    private function base64Encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64Decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    private function getCurrentUser()
    {
        return ServiceKernel::instance()->getCurrentUser();
    }

    private function getKey()
    {
        $this->getSettingService()->get('storage', array());
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
