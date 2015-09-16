<?php

namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;

$this->get

/**
 * 素材库文件上传Token
 */
class FileViewToken
{
    /**
     * @param  [type]  $fileId [description]
     * @param  [type]  $mode   'guest, logined, user'
     * @param  integer $userId [description]
     * @param  integer $ttl    [description]
     * @return [type]          [description]
     */
    public function make($fileId, $mode = 'logined', $userId = 0, $ttl = 3600)
    {
        if (!in_array($mode, array('user', 'guest'))) {
            throw new \RuntimeException('make file view token failed: `mode` is invalid.');
        }

        $userId = intval($userId);
        switch (variable) {
            case 'value':
                # code...
                break;
            default:
                # code...
                break;
        }
        if (($mode == 'user') && empty($userId)) {
            throw new \RuntimeException('make file view token failed: in user mode, userId must not empty.');
        }

        $deadline = time() + $ttl;

        $key = "{$fileId}|{$mode}|{$userId}|{$deadline}";



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

        list($userId, $targetType, $targetId, $bucket, $deadline, $sign) =  explode('|', $token);

        if ($deadline < time()) {
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

    private function base64Encode($data) { 
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 

    private function base64Decode($data) { 
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