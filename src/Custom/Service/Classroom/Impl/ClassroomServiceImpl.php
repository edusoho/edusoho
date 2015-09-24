<?php


namespace Custom\Service\Classroom\Impl;

use Classroom\Service\Classroom\Impl\ClassroomServiceImpl as BaseClassroomServiceImpl;
use Topxia\Common\ArrayToolkit;

class ClassroomServiceImpl extends BaseClassroomServiceImpl
{
    public function searchMemberCount($conditions)
    {
        $this->prepareClassroomConditions($conditions);
        return parent::searchMemberCount($conditions);
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $this->prepareClassroomConditions($conditions);
        return parent::searchMembers($conditions, $orderBy, $start, $limit);
    }

    protected function prepareClassroomConditions(&$conditions)
    {
        if(!empty($conditions['schoolOrganizationId']) && !empty($conditions['includeChildren'])){
            $userConditions['schoolOrganizationId'] = $conditions['schoolOrganizationId'];
            $userConditions['includeChildren'] = $conditions['includeChildren'];
            $count = $this->getUserService()->searchUserCount($userConditions);
            $users = $this->getUserService()->searchUsers($userConditions, array('createdTime','DESC'), 0, $count);
            $conditions['userIds'] = array_column($users, 'id');
            if(empty($conditions['userIds'])){
                $conditions['userIds'] = array(0);
            }
            unset($conditions['schoolOrganizationId']);
            unset($conditions['includeChildren']);
        }
        var_dump($conditions);
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

}