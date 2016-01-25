<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\InviteRecordDao;

class InviteRecordDaoImpl extends BaseDao implements InviteRecordDao
{
    protected $table = 'invite_record';

    private function getInviteRecord($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function findRecordsByInviteUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE inviteUserId = ? ";
        return $this->getConnection()->fetchAll($sql, array($userId)) ?: array();
    }

    public function getRecordByInvitedUserId($invitedUserId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE invitedUserId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($invitedUserId));
    }

    public function addInviteRecord($record)
    {
        $affected = $this->getConnection()->insert($this->table, $record);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert record error.');
        }

        return $this->getInviteRecord($this->getConnection()->lastInsertId());
    }

    public function updateInviteRecord($invitedUserId, $fields)
    {
        return $this->getConnection()->update($this->table, $fields, array('invitedUserId' => $invitedUserId));
    }
}
