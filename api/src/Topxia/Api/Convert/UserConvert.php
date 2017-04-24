<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class UserConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $user = ServiceKernel::instance()->createService('User:UserService')->getUser($id);
        if (empty($user)) {
            throw new \Exception('user not found');
        }
        return $user;
    }

}

