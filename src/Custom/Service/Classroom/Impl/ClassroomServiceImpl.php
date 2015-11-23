<?php

namespace Custom\Service\Classroom\Impl;

use Classroom\Service\Classroom\Impl\ClassroomServiceImpl as BaseClassroomServiceImpl;

class ClassroomServiceImpl extends BaseClassroomServiceImpl
{
    protected function _prepareClassroomConditions($conditions)
    {
        $conditions = parent::_prepareClassroomConditions($conditions);

        if (!empty($conditions['organizationId']) && !empty($conditions['includeChildren'])) {
            $userConditions['organizationId']  = $conditions['organizationId'];
            $userConditions['includeChildren'] = $conditions['includeChildren'];
            $count                             = $this->getUserService()->searchUserCount($userConditions);
            $users                             = $this->getUserService()->searchUsers($userConditions, array('createdTime', 'DESC'), 0, $count);
            $conditions['userIds']             = array_column($users, 'id');

            if (empty($conditions['userIds'])) {
                $conditions['userIds'] = array(0);
            }

            unset($conditions['organizationId']);
            unset($conditions['includeChildren']);
        }

        return $conditions;
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
