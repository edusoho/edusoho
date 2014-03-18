<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\MemberDao;
use PDO;

class MemberDao extends BaseDao implements MemberDao
{
	protected $table = 'member';

    public function searchMembers($conditions, $orderBy, $start, $limit)
    {

    }

    public function searchMembersCount($conditions)
    {
        $builder = $this->createMemberQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function createMemberQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return  $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'member')
            ->andWhere('level = :level');
    }

	public function getMember($id)
	{

	}

	public function cancelMember($id)
	{

	}

	public function addMember()
	{

	}

	public function updateMember()
	{

	}
}