<?php

namespace Topxia\Service\Thread\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Thread\Dao\ThreadMemberDao;

class ThreadMemberDaoImpl extends BaseDao implements ThreadMemberDao
{

    protected $table = 'thread_member';

    public function findActivityMembersByThreadId($threadId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ?;";

        return $this->getConnection()->fetchAll($sql, array($threadId)) ? : array();
    }

}