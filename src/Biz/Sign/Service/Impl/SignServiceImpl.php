<?php

namespace Biz\Sign\Service\Impl;

use Biz\BaseService;
use Biz\Sign\Dao\SignTargetStatisticsDao;
use Biz\Sign\Dao\SignUserLogDao;
use Biz\Sign\Dao\SignUserStatisticsDao;
use Biz\Sign\Service\SignService;
use Biz\Sign\SignException;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;

class SignServiceImpl extends BaseService implements SignService
{
    public function userSign($userId, $targetType, $targetId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            $user = $this->getUserService()->getUserByUUID($userId);
            if(empty($user)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }
        }

        $isSignedToday = $this->isSignedToday($userId, $targetType, $targetId);

        if ($isSignedToday) {
            $this->createNewException(SignException::DUPLICATE_SIGN());
        }

        $sign = [];
        $sign['userId'] = $userId;
        $sign['targetId'] = $targetId;
        $sign['targetType'] = $targetType;
        $sign['createdTime'] = time();

        $sign = $this->getSignUserLogDao()->create($sign);
        $statistics = $this->targetSignedNumIncrease($targetType, $targetId, date('Ymd', time()));
        $sign = $this->getSignUserLogDao()->update($sign['id'], ['_rank' => $statistics['signedNum']]);
        $this->refreshSignUserStatistics($userId, $targetType, $targetId, $sign);

        $this->dispatchEvent('class.signed', new Event($sign));

        return $sign;
    }

    public function isSignedToday($userId, $targetType, $targetId)
    {
        $startTimeToday = strtotime(date('y-n-d 0:0:0'));
        $endTimeToday = strtotime(date('y-n-d 23:59:59'));

        $signs = $this->getSignUserLogDao()->findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);

        return empty($signs) ? false : true;
    }

    public function isYesterdaySigned($userId, $targetType, $targetId)
    {
        $startTimeToday = strtotime(date('y-n-d 0:0:0', strtotime('-1 days')));
        $endTimeToday = strtotime(date('y-n-d 23:59:59', strtotime('-1 days')));

        $signs = $this->getSignUserLogDao()->findSignLogByPeriod($userId, $targetType, $targetId, $startTimeToday, $endTimeToday);

        return empty($signs) ? false : true;
    }

    public function findSignRecordsByPeriod($userId, $targetType, $targetId, $startDay, $endDay)
    {
        $startTime = strtotime(date('y-n-d 0:0:0', strtotime($startDay)));
        $endTime = strtotime(date('y-n-d 23:59:59', strtotime($endDay)));
        $signs = $this->getSignUserLogDao()->findSignLogByPeriod($userId, $targetType, $targetId, $startTime, $endTime);

        return $signs;
    }

    public function getSignUserStatistics($userId, $targetType, $targetId)
    {
        return $this->getSignUserStatisticsDao()->getStatisticsByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);
    }

    public function getSignTargetStatistics($targetType, $targetId, $date)
    {
        return $this->getSignTargetStatisticsDao()->getByTargetTypeAndTargetIdAndDate($targetType, $targetId, $date);
    }

    public function getTodayRank($userId, $targetType, $targetId)
    {
        $todaySign = $this->findSignRecordsByPeriod($userId, $targetType, $targetId, date('y-n-d'), date('y-n-d'));

        return $todaySign ? $todaySign['0']['_rank'] : -1;
    }

    public function countSignUserLog(array $conditions)
    {
        return $this->getSignUserLogDao()->count($conditions);
    }

    public function searchSignUserLog(array $conditions, $orderBy, $start, $limit, array $columns = [])
    {
        return $this->getSignUserLogDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    public function countSignUserStatistics(array $conditions)
    {
        return $this->getSignUserStatisticsDao()->count($conditions);
    }

    public function searchSignUserStatistics(array $conditions, $orderBy, $start, $limit, array $columns = [])
    {
        return $this->getSignUserStatisticsDao()->search($conditions, $orderBy, $start, $limit, $columns);
    }

    protected function refreshSignUserStatistics($userId, $targetType, $targetId, array $lastSign = [])
    {
        $statistics = $this->getSignUserStatisticsDao()->getStatisticsByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId);

        if ($statistics) {
            $updateFields = [
                'keepDays' => $this->isYesterdaySigned($userId, $targetType, $targetId) ? $statistics['keepDays'] + 1 : 1,
                'signDays' => $statistics['signDays'] + 1,
                'lastSignTime' => $lastSign['createdTime'],
            ];

            $statistics = $this->getSignUserStatisticsDao()->update($statistics['id'], $updateFields);
        } else {
            $statistics = [
                'userId' => $userId,
                'targetType' => $targetType,
                'targetId' => $targetId,
                'keepDays' => 1,
                'signDays' => 1,
                'createdTime' => time(),
                'lastSignTime' => $lastSign['createdTime'],
            ];

            $statistics = $this->getSignUserStatisticsDao()->create($statistics);
        }

        return $statistics;
    }

    protected function targetSignedNumIncrease($targetType, $targetId, $date)
    {
        $statistics = $this->getSignTargetStatisticsDao()->getByTargetTypeAndTargetIdAndDate($targetType, $targetId, $date);

        if ($statistics) {
            $fields = [];
            $fields['signedNum'] = $statistics['signedNum'] + 1;
            $statistics = $this->getSignTargetStatisticsDao()->update($statistics['id'], $fields);
        } else {
            $statistics['targetType'] = $targetType;
            $statistics['targetId'] = $targetId;
            $statistics['signedNum'] = 1;
            $statistics['date'] = $date;
            $statistics['createdTime'] = time();
            $statistics = $this->getSignTargetStatisticsDao()->create($statistics);
        }

        return $statistics;
    }

    /**
     * @return SignUserLogDao
     */
    protected function getSignUserLogDao()
    {
        return $this->createDao('Sign:SignUserLogDao');
    }

    /**
     * @return SignUserStatisticsDao
     */
    protected function getSignUserStatisticsDao()
    {
        return $this->createDao('Sign:SignUserStatisticsDao');
    }

    /**
     * @return SignTargetStatisticsDao
     */
    protected function getSignTargetStatisticsDao()
    {
        return $this->createDao('Sign:SignTargetStatisticsDao');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
