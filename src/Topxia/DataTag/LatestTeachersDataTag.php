<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class LatestTeachersDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取最新老师列表
     *
     * 可传入的参数：
     *   count    必需 老师数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 老师列表
     */
    public function getData(array $arguments)
    {	

        if (empty($arguments['count'])) {
            throw new \InvalidArgumentException("count参数缺失");
        }
        if ($arguments['count'] > 100) {
            throw new \InvalidArgumentException("count参数超出最大取值范围");
        }
        $conditions = array(
            'roles'=>'ROLE_TEACHER',
        );
    	return $this->getUserService()->searchUsers($conditions, array('promotedTime', 'DESC'), 0, $arguments['count']);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}


?>