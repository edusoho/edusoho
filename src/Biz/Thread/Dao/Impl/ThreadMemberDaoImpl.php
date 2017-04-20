<?php

namespace Biz\Thread\Dao\Impl;

use Biz\Thread\Dao\ThreadMemberDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadMemberDaoImpl extends GeneralDaoImpl implements ThreadMemberDao
{
    protected $table = 'thread_member';

    public function getMemberByThreadIdAndUserId($threadId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? AND userId = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($threadId, $userId)) ?: null;
    }

    public function deleteMembersByThreadId($threadId)
    {
        return $this->db()->delete($this->table, array('threadId' => $threadId));
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime',
            'updatedTime',
        );

        $declares['conditions'] = array(
            'userId = :userId',
            'threadId = :threadId',
        );

        return $declares;
    }
}
