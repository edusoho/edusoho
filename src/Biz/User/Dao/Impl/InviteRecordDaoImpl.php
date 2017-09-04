<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\InviteRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class InviteRecordDaoImpl extends GeneralDaoImpl implements InviteRecordDao
{
    protected $table = 'invite_record';

    public function findByInviteUserId($userId)
    {
        return $this->findInField('inviteUserId', array($userId));
    }

    public function findByInviteUserIds($userIds)
    {
        return $this->findInField('inviteUserId', $userIds);
    }

    public function findByInvitedUserIds($invitedUserIds)
    {
        return $this->findInField('invitedUserId', $invitedUserIds);
    }

    public function getByInvitedUserId($invitedUserId)
    {
        return $this->getByFields(array('invitedUserId' => $invitedUserId));
    }

    public function updateByInvitedUserId($invitedUserId, $fields)
    {
        return $this->db()->update($this->table, $fields, array('invitedUserId' => $invitedUserId));
    }

    public function sumCouponRateByInviteUserId($userId)
    {
        $sql = 'SELECT sum(`rate`) as rate from `coupon` where id in(SELECT `inviteUserCardId` FROM `invite_record` WHERE `inviteUserId` = ?)';

        return $this->db()->fetchColumn($sql, array($userId));
    }

    public function searchRecordGroupByInviteUserId($conditions, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
                ->select('inviteUserId, count(`invitedUserId`) as countInvitedUserId, sum(`amount`) as amount, sum(`cashAmount`) as cashAmount, sum(`coinAmount`) as coinAmount')
                ->setFirstResult($start)
                ->setMaxResults($limit)
                ->groupBy('`inviteUserId`');

        return $builder->execute()->fetchAll();
    }

    public function countInviteUser($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('count(distinct `inviteUserId`)');

        return $builder->execute()->fetchColumn();
    }

    public function countPremiumUserByInviteUserIds($userIds)
    {
        if (empty($userIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($userIds) - 1).'?';

        $sql = "SELECT count(*) as invitedUserCount, inviteUserId FROM `invite_record` where `inviteUserId` IN ({$marks}) and (`cashAmount` > 0 or `coinAmount` >0) group by `inviteUserId`";

        return $this->db()->fetchAll($sql, $userIds);
    }

    public function declares()
    {
        return array(
            'orderbys' => array('inviteTime', 'id'),
            'conditions' => array(
                'inviteUserId = :inviteUserId',
                'invitedUserId = :invitedUserId',
                'inviteUserCardId IN ( :inviteUserCardIds)',
                'inviteUserCardId <> :inviteUserCardIdNotEqual',
                'invitedUserCardId <> :invitedUserCardIdNotEqual',
                'inviteTime >= :startDateTime',
                'invitedUserId IN ( :invitedUserIds)',
                'inviteUserId IN ( :inviteUserIds)',
                'inviteTime < :endDateTime',
            ),
        );
    }
}
