<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\InviteRecordService;
use AppBundle\Common\MathToolkit;

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

    public function findByInvitedUserIds($invitedUserIds)
    {
        return $this->getInviteRecordDao()->findByInvitedUserIds($invitedUserIds);
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

    public function flushOrderInfo($conditions = array(), $start = 0, $limit = PHP_INT_MAX)
    {
        $records = $this->searchRecords(
            $conditions,
            array(),
            $start,
            $limit
        );

        foreach ($records as $record) {
            $orderInfo = $this->getOrderInfoByUserIdAndInviteTime($record['invitedUserId'], $record['inviteTime']);

            $fields['amount'] = empty($orderInfo['payAmount']) ? 0 : MathToolkit::simple($orderInfo['payAmount'], 0.01);
            $fields['cashAmount'] = empty($orderInfo['cashAmount']) ? 0 : MathToolkit::simple($orderInfo['cashAmount'], 0.01);
            $fields['coinAmount'] = empty($orderInfo['coinAmount']) ? 0 : MathToolkit::simple($orderInfo['coinAmount'], 0.01);
            $this->updateOrderInfoById($record['id'], $fields);
        }

        unset($records);
    }

    public function findByInviteUserIds($userIds)
    {
        return $this->getInviteRecordDao()->findByInviteUserIds($userIds);
    }

    public function updateOrderInfoById($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('amount', 'cashAmount', 'coinAmount'));

        return $this->getInviteRecordDao()->update($id, $fields);
    }

    public function getOrderInfoByUserIdAndInviteTime($userId, $inviteTime)
    {
        $user = $this->getCurrentUser();
        $conditions = array('user_id' => $userId, 'statuses' => array('success', 'finished'), 'pay_time_GT' => $inviteTime);

        return $this->getOrderService()->sumPaidAmount($conditions);
    }

    public function getAllUsersByRecords($records)
    {
        $inviteUserIds = ArrayToolkit::column($records, 'inviteUserId');
        $invitedUserIds = ArrayToolkit::column($records, 'invitedUserId');
        $userIds = array_merge($inviteUserIds, $invitedUserIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $users;
    }

    public function sumCouponRateByInviteUserId($userId)
    {
        return $this->getInviteRecordDao()->sumCouponRateByInviteUserId($userId);
    }

    public function searchRecordGroupByInviteUserId($conditions, $start, $limit)
    {
        $records = $this->getInviteRecordDao()->searchRecordGroupByInviteUserId($conditions, $start, $limit);

        $inviteUserIds = ArrayToolkit::column($records, 'inviteUserId');
        $users = $this->getUserService()->findUsersByIds($inviteUserIds);
        $premiumUserCounts = $this->countPremiumUserByInviteUserIds($inviteUserIds);
        $premiumUserCounts = ArrayToolkit::index($premiumUserCounts, 'inviteUserId');

        foreach ($records as &$record) {
            $record['premiumUserCounts'] = empty($premiumUserCounts[$record['inviteUserId']]) ? 0 : $premiumUserCounts[$record['inviteUserId']]['invitedUserCount'];
            $record['invitedUserNickname'] = $users[$record['inviteUserId']]['nickname'];
        }

        return $records;
    }

    public function countPremiumUserByInviteUserIds($userIds)
    {
        return $this->getInviteRecordDao()->countPremiumUserByInviteUserIds($userIds);
    }

    public function countInviteUser($conditions)
    {
        return $this->getInviteRecordDao()->countInviteUser($conditions);
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

        if (!empty($conditions['startDate'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDate']);
        }

        if (!empty($conditions['endDate'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDate']);
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

    protected function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
