<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class UserBindDataTag extends BaseDataTag implements DataTag  
{
    
    /**
     * 根据当前登录用户获取第三方绑定信息
     *
     * 可传入的参数：
     *   userId 必需 用户ID
     * 
     * @param  array $arguments 参数
     * @return array 用户
     */
    
    public function getData(array $arguments)
    {
        $userId = $this->getUserService()->getCurrentUser()->id;
        if (empty($userId)) {
            return 0;
        }
    	$userbind = $this->getUserService()->findBindsByUserId($userId);
        
        return $userbind;
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
