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

    public function flushOrderInfo($conditions = array())
    {
        $records = $this->searchRecords(
            $conditions,
            array(),
            0,
            PHP_INT_MAX
        );

        foreach($records as $record) {
            $orderInfo = $this->getOrderInfoByUserIdAndInviteTime($record['invitedUserId'], $record['inviteTime']);
            $fields['amount'] = $orderInfo['totalPrice'];
            $fields['cash_amount'] = $orderInfo['amount'];
            $fields['coin_amount'] = $orderInfo['coinAmount'];

            $this->updateOrderInfoById($record['id'], $fields);
        }
    }

    public function findByInviteUserIds($userIds)
    {
        return $this->getInviteRecordDao()->findByInviteUserIds($userIds);
    }

    public function updateOrderInfoById($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('amount', 'cash_amount', 'coin_amount'));

        return $this->getInviteRecordDao()->update($id, $fields);
    }

    public function getOrderInfoByUserIdAndInviteTime($userId, $inviteTime)
    {
        $user = $this->getCurrentUser();
        $conditions = array('userId' => $userId, 'status' => 'paid', 'paidStartTime' => $inviteTime);

        return $this->getOrderService()->analysis($conditions);
    }

    // 得到这个用户在注册后消费情况，订单消费总额；订单虚拟币总额；订单现金总额
    public function getUserOrderDataByUserIdAndTime($userId, $inviteTime)
    {
        $coinAmountTotalPrice = $this->getOrderService()->analysisCoinAmount(array('userId' => $userId, 'coinAmount' => 0, 'status' => 'paid', 'paidStartTime' => $inviteTime));
        $amountTotalPrice = $this->getOrderService()->analysisAmount(array('userId' => $userId, 'amount' => 0, 'status' => 'paid', 'paidStartTime' => $inviteTime));
        $totalPrice = $this->getOrderService()->analysisTotalPrice(array('userId' => $userId, 'status' => 'paid', 'paidStartTime' => $inviteTime));

        $coinAmountTotalPrice = round($coinAmountTotalPrice, 2);
        $amountTotalPrice = round($amountTotalPrice, 2);
        $totalPrice = round($totalPrice, 2);

        return array($coinAmountTotalPrice, $amountTotalPrice, $totalPrice);
    }

    public function getAllUsersByRecords($records)
    {
        $inviteUserIds = ArrayToolkit::column($records, 'inviteUserId');
        $invitedUserIds = ArrayToolkit::column($records, 'invitedUserId');
        $userIds = array_merge($inviteUserIds, $invitedUserIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $users;
    }

    //得到用户的邀请信息
    public function getInviteInformationsByUsers($users)
    {
        $inviteInformations = array();
        foreach ($users as $key => $user) {
            $invitedRecords = $this->findRecordsByInviteUserId($user['id']);
            $payingUserCount = 0;
            $totalPrice = 0;
            $totalCoinAmount = 0;
            $totalAmount = 0;

            foreach ($invitedRecords as $keynum => $invitedRecord) {
                list($coinAmountTotalPrice, $amountTotalPrice, $tempPrice) = $this->getUserOrderDataByUserIdAndTime($invitedRecord['invitedUserId'], $invitedRecord['inviteTime']);

                if ($coinAmountTotalPrice || $amountTotalPrice) {
                    $payingUserCount = $payingUserCount + 1;
                }

                $totalCoinAmount = $totalCoinAmount + $coinAmountTotalPrice;
                $totalAmount = $totalAmount + $amountTotalPrice;
                $totalPrice = $totalPrice + $tempPrice;
            }

            $inviteInformations[] = array(
                'id' => $user['id'],
                'nickname' => $user['nickname'],
                'payingUserCount' => $payingUserCount,
                'payingUserTotalPrice' => $totalPrice,
                'coinAmountPrice' => $totalCoinAmount,
                'amountPrice' => $totalAmount,
                'count' => count($invitedRecords),
            );
        }

        return $inviteInformations;
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

        foreach($records as &$record) {
            $record['premiumUserCounts'] = empty($premiumUserCount['invitedUserCount']) ? 0 : $premiumUserCounts[$record['inviteUserId']]['invitedUserCount'];
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
