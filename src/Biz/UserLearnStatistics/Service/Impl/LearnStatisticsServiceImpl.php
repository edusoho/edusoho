<?php

namespace Biz\UserLearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;
use AppBundle\Common\ArrayToolkit;

class LearnStatisticsServiceImpl extends BaseService implements LearnStatisticsService
{
    public function batchCreateTotalStatistics($conditions)
    {
        try {
            $this->beginTransaction();
            $statistics = $this->searchLearnData($conditions);
            $this->commit();
            $this->getTotalStatisticsDao()->batchCreate($statistics);
        } catch (\Exception $e) {
            $this->getLogger()->error('learn-statistics:'.$e->getMessage());
            $this->rollback();
        }
        
    }

    public function searchLearnData($conditions)
    {
        //学习时长
        $learnedSeconds = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId($conditions);

        //学员任务数: finishedTaskNum
        //加入班级数: joinedClassroomNum
        //退出班级数: exitClassroomNum
        //加入班级课程数: joinedClassroomCourseSetNum
        //退出班级课程数: exitClassroomCourseSetNum
        //加入班级计划数: joinedClassroomCourseNum
        //退出班级计划数: exitClassroomCourseNum
        //加入课程数: joinedCourseSetNum
        //退出课程数: exitCourseSetNum
        //加入计划数: joinedCourseNum
        //退出计划数: exitCourseNum 

        // $paidAmount = array();
        // $refundAmount = array();
        $statistics = array();

        if (!empty($conditions['userIds'])) {
            $userIds = array_unique($conditions['userIds']);
        } else {
            $userIds = array_merge($userIds, ArrayToolkit::column($learnedSeconds, 'userId'));
            $userIds = ArrayToolkit::column($finishedTaskNum, 'userId');
            $userIds = array_unique($userIds);
        }
        $statisticMap = array(
            'finishedTaskNum' => $this->getTaskResultService()->countTaskNumGroupByUserId(array_merge(array('status' => 'finish'), $conditions)),
            'joinedClassroomNum' => $this->findUserOpertateClassroomNum('join', $conditions),
            'exitClassroomNum' => $this->findUserOpertateClassroomNum('exit', $conditions),
            'joinedClassroomCourseSetNum' => $this->findUserOpertateClassroomCourseSetNum('join', $conditions),
            'exitClassroomCourseSetNum' => $this->findUserOpertateClassroomCourseSetNum('exit', $conditions),
            'joinedClassroomCourseNum' =>  $this->findUserOpertateClassroomPlanNum('join', $conditions),
            'exitClassroomCourseNum' => $this->findUserOpertateClassroomPlanNum('exit', $conditions),
            'joinedCourseSetNum' => $this->findUserOpertateCourseSetNum('join', $conditions),
            'exitCourseSetNum' => $this->findUserOpertateCourseSetNum('exit', $conditions),
            'joinedCourseNum' => $this->findUserOperateCourseNum('join', $conditions),
            'exitCourseNum' => $this->findUserOperateCourseNum('exit', $conditions),
        );
        foreach($userIds as $userId) {
            $statistic = array();
            $statistic['learnedSeconds'] = empty($learnedSeconds[$userId]) ? 0 : $learnedSeconds[$userId]['learnedTime'];
            
            foreach($statisticMap as $key => $data) {
                $statistic[$key] = empty($data[$userId]) ? 0 : $data[$userId]['count'];
            }
            $statistic['userId'] = $userId;
            $statistics[] = $statistic;
        }

        return $statistics;
    }

    private function findUserOpertateClassroomNum($operation, $conditions)
    {
        $conditions = array_merge(
            $conditions,
            array(
                'target_type' => 'classroom', 
                'operate_type' => $operation,
            )
        );
        return $this->getMemberOperationService()->countGroupByUserId('target_id', $conditions);
    }

    private function findUserOpertateClassroomCourseSetNum($operation, $conditions)
    {
        $conditions = array_merge(
            $conditions,
            array(
                'target_type' => 'course', 
                'operate_type' => $operation, 
                'parent_id_GT' => 0,
            )
        );

        return $this->getMemberOperationService()->countGroupByUserId('course_set_id', $conditions);
    }

    private function findUserOpertateClassroomPlanNum($operation, $conditions)
    {
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

    private function findUserOpertateCourseSetNum($operation, $conditions)
    {
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

    public function getStatisticsSetting()
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
    
        if (empty($syncStatisticsSetting)) {
            $syncStatisticsSetting = array();
            $syncStatisticsSetting['currentTime'] = strtotime(date("Y-m-d"), time());
            //currentTime 当天升级的那天的0点0分
            $syncStatisticsSetting['endTime'] = $syncStatisticsSetting['currentTime'] - 24*60*60*365;
            $syncStatisticsSetting['cursor'] = $syncStatisticsSetting['currentTime'];

            $this->getSettingService()->set('learn_statistics', $syncStatisticsSetting);
        }

        return $syncStatisticsSetting;
    }

    // public function syncLearnStatisticsByTime($startTime, $endTime)
    // {
    //     try {
    //         $this->beginTransaction();
    //         $finishedTaskNum = $this->getTaskResultService()->countFinishedTaskNumGroupByUserId($startTime, $endTime);
    //         $userIds = ArrayToolkit::column($finishedTaskNum, 'userId');

    //         $learnedSeconds = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId($startTime, $endTime);
    //         $userIds = array_merge($userIds, ArrayToolkit::column($learnedSeconds, 'userId'));
    //         $dailyStatistics = array();

    //         foreach($userIds as $userId) {
    //             $dailyStatistic = array();
    //             $dailyStatistic['learnedSeconds'] = empty($learnedSeconds[$userId]) ? 0 : $learnedSeconds[$userId]['learnedTime'];
    //             $dailyStatistic['finishedTaskNum'] = empty($finishedTaskNum[$userId]) ? 0 : $finishedTaskNum[$userId]['taskNum'];
    //             $dailyStatistic['userId'] = $userId;
    //             $dailyStatistics[] = $dailyStatistic;
    //         }

    //         $this->getDailyStatisticsDao()->batchCreate($dailyStatistics);

    //         $this->updateSettingCursor($endTime);
    //         $this->commit();
    //     } catch (\Exception $e) {
    //         $this->getLogger()->error('learn-statistics:'.$e->getMessage());
    //         $this->rollback();
    //         throw $e;
    //     }
    // }

    public function syncTotalLearnStatistics($conditions)
    {
          
    }

    private function updateSettingCursor($time)
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
        $syncStatisticsSetting['cursor'] = $time;
        //$syncStatisticsSetting = $this->getSettingService()->set('learn_statistics', $syncStatisticsSetting);
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
        return $this->biz->dao('UserLearnStatistics:DailyStatisticsDao');
    }

    protected function getTotalStatisticsDao()
    {
        return $this->biz->dao('UserLearnStatistics:TotalStatisticsDao');
    }
}