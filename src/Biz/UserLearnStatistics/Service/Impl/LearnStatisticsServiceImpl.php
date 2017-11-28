<?php

namespace Biz\UserLearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;
use AppBundle\Common\ArrayToolkit;

class LearnStatisticsServiceImpl extends BaseService implements LearnStatisticsService
{
    public function searchTotalStatistics($conditions, $order, $start, $limit)
    {
        return $this->getTotalStatisticsDao()->search($conditions, $order, $start, $limit);
    }

    public function countTotalStatistics($conditions)
    {
        return $this->getTotalStatisticsDao()->count($conditions);
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
                'recordTime' => $conditions['createdTime_LT'],
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
                'recordTime' => $conditions['createdTime_LT'],
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

    public function searchLearnData($conditions, $fields)
    {
        if (!ArrayToolkit::requireds($conditions, array('createdTime_GE', 'createdTime_LT'))) {
            throw $this->createInvalidArgumentException('Invalid Arguments');
        }

        $learnedSeconds = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId($conditions);
        $payAmount = $this->findUserPaidAmount($underlineConditions);
        $refundAmount = $this->findUserRefundAmount($underlineConditions);

        $statistics = array();
        if (!empty($conditions['userIds'])) {
            $userIds = array_unique($conditions['userIds']);
        }

        $statisticMap = array(
            'finishedTaskNum' => $this->getTaskResultService()->countTaskNumGroupByUserId(array_merge(array('status' => 'finish'), $conditions)),
            'joinedClassroomNum' => $this->findUserOperateClassroomNum('join', $underlineConditions),
            'exitClassroomNum' => $this->findUserOperateClassroomNum('exit', $underlineConditions),
            'joinedClassroomCourseNum' =>  $this->findUserOperateClassroomPlanNum('join', $underlineConditions),
            'joinedCourseSetNum' => $this->findUserOperateCourseSetNum('join', $underlineConditions),
            'exitCourseSetNum' => $this->findUserOperateCourseSetNum('exit', $underlineConditions),
            'joinedCourseNum' => $this->findUserOperateCourseNum('join', $underlineConditions),
            'exitCourseNum' => $this->findUserOperateCourseNum('exit', $underlineConditions),
        );

        if (!isset($userIds)) {
            $userIds = array();
            foreach($statisticMap as $key => $data) {
                $userIds = array_merge($userIds, array_keys($data));
            }
            $userIds = array_merge($userIds, array_keys($learnedSeconds));
            $userIds = array_merge($userIds, array_keys($payAmount));
            $userIds = array_merge($userIds, array_keys($refundAmount));

            $userIds = array_unique($userIds);
        }

        foreach($userIds as $userId) {
            $statistic = array();
            $statistic['learnedSeconds'] = empty($learnedSeconds[$userId]) ? 0 : $learnedSeconds[$userId]['learnedTime'];
            $statistic['paidAmount'] = empty($payAmount[$userId]) ? 0 : $payAmount[$userId]['amount'];
            $statistic['refundAmount'] = empty($refundAmount[$userId]) ? 0 : $refundAmount[$userId]['amount'];
            $statistic['actualAmount'] = $statistic['paidAmount']  - $statistic['refundAmount'];
            foreach($statisticMap as $key => $data) {
                $statistic[$key] = empty($data[$userId]) ? 0 : $data[$userId]['count'];
            }
            $statistic['userId'] = $userId;
            $statistic = array_merge($statistic, $fields);
            $statistics[] = $statistic;
        }

        return $statistics;
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

    private function findUserOperateClassroomPlanNum($operation, $conditions)
    {
        $conditions = $this->buildMemberOperationConditions($conditions);
        $conditions = array_merge(
            $conditions,
            array(
                'target_type' => 'course', 
                'operate_type' => $operation, 
                'parent_id_GT' => 0,
            )
        );

        return $this->getMemberOperationService()->countGroupByUserId('target_id', $conditions); 
    }

    private function findUserOperateCourseSetNum($operation, $conditions)
    {
        if (empty($conditions['skipSyncCourseSetNum'])){
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

    public function getStatisticsSetting()
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
    
        if (empty($syncStatisticsSetting)) {
            $syncStatisticsSetting = array();
            $syncStatisticsSetting['currentTime'] = strtotime(date("Y-m-d"), time());
            //currentTime 当天升级的那天的0点0分
            $syncStatisticsSetting['timespan'] = 24*60*60*365;

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
}