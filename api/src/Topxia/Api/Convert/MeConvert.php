<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class MeConvert implements Convert
{
    //根据token获取当前用户完整数据
    public function convert($token)
    {
        $token = ServiceKernel::instance()->createService('User:UserService')->getToken('mobile_login',$token);
        if (empty($token)) {
            return array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' =>  '',
                'roles' => array(),
            );
        }
        
        $user = ServiceKernel::instance()->createService('User:UserService')->getUser($token['userId']);
        if (empty($user)) {
            return array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' =>  '',
                'roles' => array(),
            );
        }
        return $user;
    }

}

