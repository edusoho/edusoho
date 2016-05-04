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

    public function searchRecordCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchRecords($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    private function _createSearchQueryBuilder($conditions)
    {
        $tmpConditions = array();

        if (isset($conditions['inviteUserCardIdNotEqual'])) {
            $tmpConditions['inviteUserCardIdNotEqual'] = $conditions['inviteUserCardIdNotEqual'];
        }

        if (isset($conditions['invitedUserCardIdNotEqual'])) {
            $tmpConditions['invitedUserCardIdNotEqual'] = $conditions['invitedUserCardIdNotEqual'];
        }

        $conditions = array_merge($conditions, $tmpConditions);

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'invite_record')
            ->andWhere('inviteUserId = :inviteUserId')
            ->andWhere('invitedUserId = :invitedUserId')
            ->andWhere('inviteUserCardId IN ( :inviteUserCardIds)')
            ->andWhere('inviteUserCardId <> :inviteUserCardIdNotEqual')
            ->andWhere('invitedUserCardId <> :invitedUserCardIdNotEqual')
            ->andWhere('inviteTime >= :startDateTime')
            ->andWhere('invitedUserId IN ( :invitedUserIds)')
            ->andWhere('inviteTime < :endDateTime');
    }
}
