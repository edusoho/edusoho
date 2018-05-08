<?php

namespace AppBundle\Util;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\TimeMachine;

class UploadToken
{
    public function make($group, $type = 'image', $duration = 18000)
    {
        $user = $this->getCurrentUser();
        $deadline = TimeMachine::time() + $duration;
        $secret = $this->getServiceKernel()->getParameter('secret');
        $key = "{$user['id']}|{$group}|{$type}|{$deadline}";
        $sign = md5("{$key}|{$secret}");

        return $this->base64Encode("{$key}|{$sign}");
    }

    public function parse($token)
    {
        $token = $this->base64Decode($token);
        if (empty($token)) {
            return null;
        }

        list($userId, $group, $type, $deadline, $sign) = explode('|', $token);

        if ($deadline < TimeMachine::time()) {
            return null;
        }

        $secret = $this->getServiceKernel()->getParameter('secret');
        $expectedSign = md5("{$userId}|{$group}|{$type}|{$deadline}|{$secret}");
        if ($sign != $expectedSign) {
            return null;
        }

        return array(
            'userId' => $userId,
            'group' => $group,
            'type' => $type,
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
