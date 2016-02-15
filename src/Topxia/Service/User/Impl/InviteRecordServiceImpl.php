<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\InviteRecordService;

class InviteRecordServiceImpl extends BaseService implements InviteRecordService
{
    public function findRecordsByInviteUserId($userId)
    {
        return $this->getInviteRecordDao()->findRecordsByInviteUserId($userId);
    }

    public function createInviteRecord($inviteUserId, $invitedUserId)
    {
        $record = array(
            'inviteUserId'  => $inviteUserId,
            'invitedUserId' => $invitedUserId,
            'inviteTime'    => time()
        );
        return $this->getInviteRecordDao()->addInviteRecord($record);
    }

    public function getRecordByInvitedUserId($invitedUserId)
    {
        return $this->getInviteRecordDao()->getRecordByInvitedUserId($invitedUserId);
    }

    public function addInviteRewardRecordToInvitedUser($invitedUserId, $fields)
    {
        return $this->getInviteRecordDao()->updateInviteRecord($invitedUserId, $fields);
    }

    public function addInviteRewardRecordToInviteUser($invitedUserId, $fields)
    {
        return $this->getInviteRecordDao()->updateInviteRecord($invitedUserId, $fields);
    }

    private function getInviteRecordDao()
    {
        return $this->createDao('User.InviteRecordDao');
    }
}
