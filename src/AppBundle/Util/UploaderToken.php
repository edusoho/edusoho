<?php

namespace AppBundle\Util;

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
        $deadline = TimeMachine::time() + $ttl;
        $key = "{$user['id']}|{$targetType}|{$targetId}|{$bucket}|{$deadline}";
        $sign = md5("{$key}|{$user['salt']}");

        return $this->base64Encode("{$key}|{$sign}");
    }

    public function parse($token)
    {
        $token = $this->base64Decode($token);
        if (empty($token)) {
            return null;
        }

        list($userId, $targetType, $targetId, $bucket, $deadline, $sign) = explode('|', $token);

        if ($deadline < TimeMachine::time()) {
            return null;
        }

        $user = $this->getCurrentUser();

        $expectedSign = md5("{$userId}|{$targetType}|{$targetId}|{$bucket}|{$deadline}|{$user['salt']}");
        if ($sign != $expectedSign) {
            return null;
        }

        return array(
            'userId' => $userId,
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

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
