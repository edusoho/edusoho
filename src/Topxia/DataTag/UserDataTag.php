<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class UserDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取一个用户
     *
     * 可传入的参数：
     *   userId 必需 用户ID
     * 
     * @param  array $arguments 参数
     * @return array 用户
     */
    
    public function getData(array $arguments)
    {
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException("userId参数缺失");
        }
    	return $this->getUserService()->getUser($arguments['userId']);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}


?>