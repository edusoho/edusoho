<?php

namespace AppBundle\Extensions\DataTag;

class LatestLoginUsersDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最近登录用户列表.
     *
     * 可传入的参数：
     *   count    必需 用户数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 用户列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $users = $this->getUserService()->searchUsers(array(), array('loginTime' => 'DESC'), 0, $arguments['count']);

        return $this->unsetUserPasswords($users);
    }
}
