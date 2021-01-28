<?php

namespace AppBundle\Extensions\DataTag;

class LatestUsersDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新用户列表.
     *
     * 可传入的参数：
     *   onlyMember 可选　true时，只返回普通用户
     *   count    必需 用户数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 用户列表
     */
    public function getData(array $arguments)
    {
        $conditions = array();
        if (!empty($arguments['onlyMember'])) {
            $conditions['role'] = 'ROLE_USER';
        }

        $this->checkCount($arguments);
        $users = $this->getUserService()->searchUsers($conditions, array('createdTime' => 'DESC'), 0, $arguments['count']);

        return $this->unsetUserPasswords($users);
    }
}
