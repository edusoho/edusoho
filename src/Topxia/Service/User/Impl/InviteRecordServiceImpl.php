<?php
namespace Topxia\Service\User\Impl;

use Topxia\Common\ArrayToolkit;
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

    public function searchRecordCount($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getInviteRecordDao()->searchRecordCount($conditions);
    }

    public function searchRecords($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getInviteRecordDao()->searchRecords($conditions, $orderBy, $start, $limit);
    }

    private function _prepareConditions($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value == 0) {
                return true;
            }

            return !empty($value);
        }

        );

        if (array_key_exists('nickname', $conditions)) {
            if ($conditions['nickname']) {
                $users = $this->getUserService()->searchUsers(array('nickname' => $conditions['nickname']), array('createdTime', 'DESC'), 0, PHP_INT_MAX);

                $conditions['invitedUserIds'] = empty($users) ? -1 : ArrayToolkit::column($users, 'id');
            }
        }

        return $conditions;
    }

    private function getInviteRecordDao()
    {
        return $this->createDao('User.InviteRecordDao');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
