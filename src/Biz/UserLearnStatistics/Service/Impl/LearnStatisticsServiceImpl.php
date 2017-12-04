<?php

namespace Biz\UserLearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;
use AppBundle\Common\ArrayToolkit;

class LearnStatisticsServiceImpl extends BaseService implements LearnStatisticsService
{
    public function statisticsDataSearch($conditions)
    {
        list($conditions, $order, $daoType) = $this->analysisCondition($conditions);
        return $this->getStatisticsDao($daoType)->statisticSearch($conditions, $order);
    }

    public function statisticsDataCount($conditions)
    {
        list($conditions, $order, $daoType) = $this->analysisCondition($conditions);
        return $this->getStatisticsDao($daoType)->statisticCount($conditions);
    }

    public function searchTotalStatistics($conditions, $order, $start, $limit)
    {
        return $this->getTotalStatisticsDao()->search($conditions, $order, $start, $limit);
    }

    public function countTotalStatistics($conditions)
    {
        return $this->getTotalStatisticsDao()->count($conditions);
    }

    public function searchDailyStatistics($conditions, $order, $start, $limit)
    {
        return $this->getDailyStatisticsDao()->search($conditions, $order, $start, $limit);
    }

    public function batchCreateTotalStatistics($conditions)
    {
        try {
            $this->beginTransaction();
            $statistics = $this->searchLearnData($conditions);
            $this->getTotalStatisticsDao()->batchCreate($statistics);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateTotalStatistics:'.$e->getMessage(), $conditions);
            $this->rollback();
            throw $e;
        }
    }

    public function batchCreatePastDailyStatistics($conditions)
    {
        try {
            $this->beginTransaction();
            $fields = array(
                'isStorage' => 1,
                'recordTime' => $conditions['createdTime_GE'],
            );
            $statistics = $this->searchLearnData($conditions, $fields);
            $this->getDailyStatisticsDao()->batchCreate($statistics);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreatePastDailyStatistics:'.$e->getMessage(), $conditions);
            $this->rollback();
            throw $e;
        }
    }

    public function batchCreateDailyStatistics($conditions)
    {
        try {
            $this->beginTransaction();
            $fields = array(
                'recordTime' => $conditions['createdTime_GE'],
            );
            $statistics = $this->searchLearnData($conditions, $fields);
            $this->getDailyStatisticsDao()->batchCreate($statistics);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchCreateDailyStatistics:'.$e->getMessage(), $conditions);
            $this->rollback();
            throw $e;
        }
    }

    public function batchDelatePastDailyStatistics($conditions)
    {
        try {
            $this->beginTransaction();
            $this->getDailyStatisticsDao()->batchDelete($conditions);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('batchDelatePastDailyStatistics:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }
    }

    public function searchLearnData($conditions, $fields = array())
    {
        if (!ArrayToolkit::requireds($conditions, array('createdTime_GE', 'createdTime_LT'))) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        $learnedSeconds = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId($conditions);
        $payAmount = $this->findUserPaidAmount($conditions);
        $refundAmount = $this->findUserRefundAmount($conditions);

        $statistics = array();
        if (!empty($conditions['userIds'])) {
            $userIds = array_unique($conditions['userIds']);
        }

        $statisticMap = array(
            'finishedTaskNum' => $this->getTaskResultService()->countTaskNumGroupByUserId(array_merge(array('status' => 'finish'), $conditions)),
            'joinedClassroomNum' => $this->findUserOperateClassroomNum('join', $conditions),
            'exitClassroomNum' => $this->findUserOperateClassroomNum('exit', $conditions),
            'joinedCourseSetNum' => $this->findUserOperateCourseSetNum('join', $conditions),
            'exitCourseSetNum' => $this->findUserOperateCourseSetNum('exit', $conditions),
            'joinedCourseNum' => $this->findUserOperateCourseNum('join', $conditions),
            'exitCourseNum' => $this->findUserOperateCourseNum('exit', $conditions),
        );

        if (!isset($userIds)) {
            $userIds = array();
            foreach ($statisticMap as $key => $data) {
                $userIds = array_merge($userIds, array_keys($data));
            }
            $userIds = array_merge($userIds, array_keys($learnedSeconds));
            $userIds = array_merge($userIds, array_keys($payAmount));
            $userIds = array_merge($userIds, array_keys($refundAmount));

            $userIds = array_unique($userIds);
        }

        foreach ($userIds as $userId) {
            if ($userId == 0) {
                continue;
            }
            $statistic = array();
            $statistic['learnedSeconds'] = empty($learnedSeconds[$userId]) ? 0 : $learnedSeconds[$userId]['learnedTime'];
            $statistic['paidAmount'] = empty($payAmount[$userId]) ? 0 : $payAmount[$userId]['amount'];
            $statistic['refundAmount'] = empty($refundAmount[$userId]) ? 0 : $refundAmount[$userId]['amount'];
            $statistic['actualAmount'] = $statistic['paidAmount'] - $statistic['refundAmount'];
            foreach ($statisticMap as $key => $data) {
                $statistic[$key] = empty($data[$userId]) ? 0 : $data[$userId]['count'];
            }
            $statistic['userId'] = $userId;
            $statistic = array_merge($statistic, $fields);
            $statistics[] = $statistic;
        }

        return $statistics;
    }

    private function analysisCondition($conditions)
    {
        if (!empty($conditions['isDefault']) && $conditions['isDefault'] == 'true') {
            $orderBy = array('userId' => 'DESC', 'joinedCourseNum' => 'DESC', 'actualAmount' => 'DESC');
        } else {
            $orderBy = array('id' => 'DESC');
        }

        $conditions = ArrayToolkit::parts($conditions, array('startDate', 'endDate', 'userIds'));
        if (!empty($conditions['startDate']) || !empty($conditions['endDate'])) {
            $daoType = 'Daily';
            $conditions['recordTime_GE'] = !empty($conditions['startDate']) ? strtotime($conditions['startDate']) : strtotime($this->getTimespan());
            $conditions['recordTime_LE'] = !empty($conditions['endDate']) ? strtotime($conditions['endDate']) : strtotime(date('Y-m-d', time()));
            unset($conditions['startDate']);
            unset($conditions['endDate']);
        } else {
            $daoType = 'Total';
        }

        return array($conditions,  $orderBy, $daoType);
    }

    public function getTimespan()
    {
        $settings = $this->getSettingService()->get('learn_statistics');
        if (!empty($settings) && $settings['timespan'] == strtotime('1971/1/1 8:0:0')) {
            $settings['timespan'] = 24*60*60*365;
        }

        return date('Y-m-d', time() - $settings['timespan']);
    }
    
    public function storageDailyStatistics($limit = 1000)
    {
        try {
            $this->beginTransaction();
            $dailyData = $this->searchDailyStatistics(
                array('isStorage' => 0),
                array('id' => 'asc'),
                0,
                $limit
            );
            $learnSetting = $this->getStatisticsSetting();
            if (empty($dailyData) || empty($learnSetting['syncTotalDataStatus'])) {
                return;
            }

            $dailyUserIds = ArrayToolkit::column($dailyData, 'userId');

            $totalData = $this->searchTotalStatistics(array('userIds' => $dailyUserIds), array(), 0, PHP_INT_MAX);
            $totalData = ArrayToolkit::index($totalData, 'userId');
            $totalUserIds = array_keys($totalData);

            $addTotalData = $updateTotalData = array();
            $updateColumn = array(
                'joinedClassroomNum',
                'joinedCourseSetNum',
                'joinedCourseNum',
                'exitClassroomNum',
                'exitClassroomCourseSetNum',
                'exitCourseSetNum',
                'exitCourseNum',
                'exitClassroomCourseNum',
                'learnedSeconds',
                'finishedTaskNum',
                'paidAmount',
                'refundAmount',
                'actualAmount',
            );
            foreach ($dailyData as $data) {
                unset($data['recordTime']);
                unset($data['isStorage']);
                if (in_array($data['userId'], $totalUserIds)) {
                    $userId = $data['userId'];
                    if (!isset($updateTotalData[$userId])) {
                        $updateTotalData[$userId] = $totalData[$userId];
                    }
                    //有数据，做累加
                    foreach ($updateColumn as $column) {
                        $updateTotalData[$userId][$column] += $data[$column];
                    }
                } else {
                    //无数据，做新增
                    unset($data['id']);
                    $addTotalData[] = $data;
                }
            }
            if (!empty($addTotalData)) {
                $this->getTotalStatisticsDao()->batchCreate($addTotalData);
            }

            if (!empty($updateTotalData)) {
                $this->getTotalStatisticsDao()->batchUpdate(array_keys($updateTotalData), $updateTotalData, 'userId');
            }
            $this->updateStorageByIds(ArrayToolkit::column($dailyData, 'id'));
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('storageDailyStatistics:'.$e->getMessage(), $conditions);
            $this->rollback();
            throw $e;
        }
    }

    private function findUserOperateClassroomNum($operation, $conditions)
    {
        $conditions = $this->buildMemberOperationConditions($conditions);
        $conditions = array_merge(
            $conditions,
            array(
                'target_type' => 'classroom',
                'operate_type' => $operation,
            )
        );

        return $this->getMemberOperationService()->countGroupByUserId('target_id', $conditions);
    }

    private function findUserOperateCourseSetNum($operation, $conditions)
    {
        if (empty($conditions['skipSyncCourseSetNum'])) {
            return array();
        }

        $conditions = $this->buildMemberOperationConditions($conditions);
        $conditions = array_merge(
            $conditions,
            array(
                'target_type' => 'course',
                'operate_type' => $operation,
                'parent_id' => 0,
            )
        );
        $operation == 'join' ? $conditions['join_course_set'] = 1 : $conditions['exit_course_set'] = 1;

        return $this->getMemberOperationService()->countGroupByUserId('course_set_id', $conditions);
    }

    private function findUserOperateCourseNum($operation, $conditions)
    {
        $conditions = $this->buildMemberOperationConditions($conditions);
        $conditions = array_merge(
            $conditions,
            array(
                'target_type' => 'course',
                'operate_type' => $operation,
                'parent_id' => 0,
            )
        );

        return $this->getMemberOperationService()->countGroupByUserId('target_id', $conditions);
    }

    private function findUserPaidAmount($conditions)
    {
        $cashflowConditions = $this->buildCashflowConditions($conditions);
        $cashflowConditions = array_merge($cashflowConditions, array(
            'type' => 'outflow',
            'amount_type' => 'money',
            'except_user_id' => 0,
        ));

        return $this->getAccountService()->sumAmountGroupByUserId($cashflowConditions);
    }

    private function findUserRefundAmount($conditions)
    {
        $cashflowConditions = $this->buildCashflowConditions($conditions);
        $cashflowConditions = array_merge($cashflowConditions, array(
            'type' => 'inflow',
            'amount_type' => 'money',
            'except_user_id' => 0,
            'action' => 'refund',
        ));

        return $this->getAccountService()->sumAmountGroupByUserId($cashflowConditions);
    }

    private function buildCashflowConditions($conditions)
    {
        $cashflowConditions['created_time_GTE'] = $conditions['createdTime_GE'];
        $cashflowConditions['created_time_LT'] = $conditions['createdTime_LT'];
        if (!empty($conditions['userIds'])) {
            $cashflowConditions['user_ids'] = $conditions['userIds'];
        }

        return $cashflowConditions;
    }

    private function buildMemberOperationConditions($conditions)
    {
        $newConditions['created_time_GE'] = $conditions['createdTime_GE'];
        $newConditions['created_time_LT'] = $conditions['createdTime_LT'];
        if (!empty($conditions['userIds'])) {
            $newConditions['user_ids'] = $conditions['userIds'];
        }

        return $newConditions;
    }

    public function updateStorageByIds($ids)
    {
        return $this->getDailyStatisticsDao()->updateStorageByIds($ids);
    }

    public function getStatisticsSetting()
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');

        if (empty($syncStatisticsSetting)) {
            $syncStatisticsSetting = array();
            $syncStatisticsSetting['currentTime'] = strtotime(date('Y-m-d'), time());
            //currentTime 当天升级的那天的0点0分
            $syncStatisticsSetting['timespan'] = 24 * 60 * 60 * 365;

            $this->getSettingService()->set('learn_statistics', $syncStatisticsSetting);
        }

        return $syncStatisticsSetting;
    }

    protected function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }

    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:DailyStatisticsDao');
    }

    protected function getTotalStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:TotalStatisticsDao');
    }

    protected function getStatisticsDao($daoType)
    {
        return $this->createDao("UserLearnStatistics:{$daoType}StatisticsDao");
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
