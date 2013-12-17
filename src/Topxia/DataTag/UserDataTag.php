<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class UserDataTag extends CourseBaseDataTag implements DataTag  
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
        $this->checkUserId($arguments);

    	$user = $this->getUserService()->getUser($arguments['userId']);
        $user['password'] = NULL;
        $user['salt'] = NULL;
        
        return $user;
    }

}
