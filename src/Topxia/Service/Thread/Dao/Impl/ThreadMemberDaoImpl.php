<?php

namespace Topxia\Service\Thread\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Thread\Dao\ThreadMemberDao;

class ThreadMemberDaoImpl extends BaseDao implements ThreadMemberDao
{

    protected $table = 'thread_member';

    public function getMember($memberId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($memberId)) ?: null;
    }

    public function getMemberByThreadIdAndUserId($threadId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? AND userId = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($threadId, $userId)) ?: null;
    }

    public function addMember($member)
    {
        $affected = $this->getConnection()->insert($this->table, $member);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert thread member error.');
        }

        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function deleteMember($memberId)
    {
        return $this->getConnection()->delete($this->table, array('id' => $memberId));
    }

    public function findMembersCountByThreadId($threadId)
    {
        $sql = "SELECT count(1) FROM {$this->table} WHERE threadId = ?;";

        return $this->getConnection()->fetchAssoc($sql, array($threadId));
    }

    public function findMembersByThreadId($threadId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ?;";

        return $this->getConnection()->fetchAll($sql, array($threadId)) ?: array();
    }
}
