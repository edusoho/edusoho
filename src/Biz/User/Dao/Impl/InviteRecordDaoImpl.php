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

    public function getByInvitedUserId($invitedUserId)
    {
        return $this->getByFields(array('invitedUserId' => $invitedUserId));
    }

    public function updateByInvitedUserId($invitedUserId, $fields)
    {
        return $this->db()->update($this->table, $fields, array('invitedUserId' => $invitedUserId));
    }

    // private function _createQueryBuilder($conditions)
    // {
    //     $tmpConditions = array();

    //     if (isset($conditions['inviteUserCardIdNotEqual'])) {
    //         $tmpConditions['inviteUserCardIdNotEqual'] = $conditions['inviteUserCardIdNotEqual'];
    //     }

    //     if (isset($conditions['invitedUserCardIdNotEqual'])) {
    //         $tmpConditions['invitedUserCardIdNotEqual'] = $conditions['invitedUserCardIdNotEqual'];
    //     }

    //     $conditions = array_merge($conditions, $tmpConditions);

    //     return $this->_getQueryBuilder($conditions)
    //         ->from($this->table, 'invite_record')
    //         ->andWhere('inviteUserId = :inviteUserId')
    //         ->andWhere('invitedUserId = :invitedUserId')
    //         ->andWhere('inviteUserCardId IN ( :inviteUserCardIds)')
    //         ->andWhere('inviteUserCardId <> :inviteUserCardIdNotEqual')
    //         ->andWhere('invitedUserCardId <> :invitedUserCardIdNotEqual')
    //         ->andWhere('inviteTime >= :startDateTime')
    //         ->andWhere('invitedUserId IN ( :invitedUserIds)')
    //         ->andWhere('inviteTime < :endDateTime');
    // }

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
                'inviteTime < :endDateTime',
            ),
        );
    }
}
