<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class MeConvert implements Convert
{
    //根据token获取当前用户完整数据
    public function convert($token)
    {
        $token = ServiceKernel::instance()->createService('User.UserService')->getToken('login',$token);
        if (empty($token)) {
            throw new \Exception('token not found');
        }
        
        $user = ServiceKernel::instance()->createService('User.UserService')->getUser($token['userId']);
        if (empty($user)) {
            throw new \Exception('user not found');
        }
        return $user;
    }

}

