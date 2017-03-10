<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\InviteRecordService;

class InviteRecordServiceImpl extends BaseService implements InviteRecordService
{
    public function findRecordsByInviteUserId($userId)
    {
        return $this->getInviteRecordDao()->findByInviteUserId($userId);
    }

    public function createInviteRecord($inviteUserId, $invitedUserId)
    {
        $record = array(
            'inviteUserId' => $inviteUserId,
            'invitedUserId' => $invitedUserId,
            'inviteTime' => time(),
        );

        return $this->getInviteRecordDao()->create($record);
    }

    public function getRecordByInvitedUserId($invitedUserId)
    {
        return $this->getInviteRecordDao()->getByInvitedUserId($invitedUserId);
    }

    public function addInviteRewardRecordToInvitedUser($invitedUserId, $fields)
    {
        return $this->getInviteRecordDao()->updateByInvitedUserId($invitedUserId, $fields);
    }

    public function countRecords($conditions)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getInviteRecordDao()->count($conditions);
    }

    public function searchRecords($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);

        return $this->getInviteRecordDao()->search($conditions, $orderBy, $start, $limit);
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
                $users = $this->getUserService()->searchUsers(array('nickname' => $conditions['nickname']), array('createdTime' => 'DESC'), 0, PHP_INT_MAX);

                $conditions['invitedUserIds'] = empty($users) ? -1 : ArrayToolkit::column($users, 'id');
            }
        }

        return $conditions;
    }

    private function getInviteRecordDao()
    {
        return $this->createDao('User:InviteRecordDao');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
