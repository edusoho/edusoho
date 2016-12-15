<?php


namespace Biz\Group\Service\Impl;


use Biz\BaseService;
use Biz\Group\Dao\GroupDao;
use Biz\Group\Dao\GroupMemberDao;
use Biz\Group\Dao\MemberDao;
use Biz\Group\Service\GroupService;
use Topxia\Common\ArrayToolkit;

class GroupServiceImpl extends BaseService implements GroupService
{
    public function countMembers($conditions)
    {
        $count = $this->getGroupMemberDao()->count($conditions);
        return $count;
    }

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        return $this->getGroupMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function getGroupsByIds($ids)
    {
        $groups = $this->getGroupDao()->getGroupsByIds($ids);
        return ArrayToolkit::index($groups, 'id');
    }

    /**
     * @return MemberDao
     */
    protected function getGroupMemberDao()
    {
        return $this->createDao('Group:MemberDao');
    }

    /**
     * @return GroupDao
     */
    protected function getGroupDao()
    {
        return $this->createDao('Group:GroupDao');
    }
}