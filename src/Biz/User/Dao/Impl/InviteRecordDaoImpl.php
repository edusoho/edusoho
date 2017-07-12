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

    public function declares()
    {
        return array(
            'orderbys' => array('inviteTime'),
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
