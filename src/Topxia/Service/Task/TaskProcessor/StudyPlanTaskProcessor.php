<?php
namespace Topxia\Service\Task\TaskProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Exception;

class StudyPlanTaskProcessor implements TaskProcessor
{
    public function addTask(array $fields)
    {
        return ;
    }

    public function addBatchTasks(array $batchFields)
    {
        $classroomId = $batchFields['classroomId'];
        $userId = $batchFields['userId'];

        $plan = $this->getClassroomPlanService()->getPlanByClassroomId($classroomId);
        if (!$plan){
            throw new Exception("该学习计划不存在!");
        }

        $planTasks = $this->getClassroomPlanTaskService()->findTasksByPlanId($plan['id']);
        $planMember = $this->getClassroomPlanMemberService()->getPlanMemberByPlanId($plan['id'], $userId);
        

        if ($planTasks) {
            $taskCourseIds = array_unique(ArrayToolkit::column($planTasks, 'courseId'));

            $this->_prepareTaskAddInfo($planTasks, $plan, $userId, $taskCourseIds, $planMember);
        }

        return true;
    }

    public function updateUserTasks($userId, $batchId)
    {
        $plan = $this->getClassroomPlanService()->getPlan($batchId);

        $planTasks = $this->getClassroomPlanTaskService()->findTasksByPlanId($plan['id']);
        $planMember = $this->getClassroomPlanMemberService()->getPlanMemberByPlanId($plan['id'], $userId);

        if ($planTasks) {
            $taskCourseIds = array_unique(ArrayToolkit::column($planTasks, 'courseId'));

            $this->_prepareTaskAddInfo($planTasks, $plan, $userId, $taskCourseIds, $planMember, 'update');
        }

        return true;
    }

    public function finishTask(array $targetObject, $userId)
    {
        $conditions = array(
            'userId' => $userId,
            'taskType' => 'studyplan',
            'targetId' => $targetObject['id'],
            'targetType' => $targetObject['type'],
            'status' => 'active'
        );
        $getTask = $this->getTaskService()->getTaskByParams($conditions);

        if ($getTask) {
            $canFinished = $this->_canFinished($getTask, $targetObject);

            if ($canFinished) {
                $updateInfo = array('status'=>'completed', 'completedTime'=>time());
                return $this->getTaskService()->updateTask($getTask['id'], $updateInfo);
            }
            
        }

        return array();
    }

    public function canFinish($targetId, $targetType, $userId)
    {
        $conditions = array(
            'userId' => $userId,
            'taskType' => 'studyplan',
            'targetId' => $targetId,
            'targetType' => $targetType,
            'status' => 'active'
        );
        $userTask = $this->getTaskService()->getTaskByParams($conditions);

        if ($userTask) {
            $beforeActiveUserTaskCount = $this->getTaskService()->searchTaskCount(array(
                'userId' => $userId,
                'taskType' => 'studyplan',
                'batchId' => $userTask['batchId'],
                'status' => 'active',
                'taskStartTimeLessThan' => $userTask['taskStartTime'],
            ));

            if ($beforeActiveUserTaskCount > 0) {
                return false;
            }
        }

        return true;
    }
    
    protected function _prepareTaskAddInfo($planTasks, $plan, $userId, $courseIds, $planMember, $type='add')
    {
        $this->getTaskDao()->getConnection()->beginTransaction();
        try {
            
            $newTaskIds = $previewTaskInfo = array();
            $dayLoop = $j = 0;
            $perDayStudyTime = 0;
            $planStartTime = $plan['setTime'] ? $plan['planStartTime'] : $planMember['createdTime'];
            $availableHours = $planMember['metas']['availableHours'];
            $availableDate = $planMember['metas']['availableDate'];
            $targetDays = $planMember['metas']['targetWeeks'] * 7;

            foreach ($planTasks as $key => $planTask) {

                $taskInfo['userId'] = $userId;
                $taskInfo['taskType'] = 'studyplan';
                $taskInfo['createdTime'] = time();
                $taskInfo['title'] = $planTask['title'];
                $taskInfo['batchId'] = $plan['id'];
                $taskInfo['targetId'] = $planTask['objectId'];
                $taskInfo['targetType'] = $planTask['type'];
                $taskInfo['required'] = 0;
                $taskInfo['meta']['classroomId'] = $plan['classroomId'];
                $taskInfo['meta']['courseId'] = $planTask['courseId'];
                $taskInfo['meta']['phaseId'] = $planTask['phaseId'];
                $taskInfo['meta']['lessonId'] = $planTask['meta']['lessonId'];
                $taskInfo['meta']['lessonTitle'] = $planTask['meta']['lessonTitle'];
                $taskInfo['status'] = 'active';

                $perDayStudyTime += $planTask['suggestHours'];

                //获取任务执行时间
                /*for ($dayLoop; $dayLoop <= $targetDays; $dayLoop++) {

                    $taskStartTime = $dayLoop == 0 ? $planStartTime : strtotime("+{$dayLoop} day", $planStartTime);

                    $weekDay = date('w', $taskStartTime);
                    if (!in_array($weekDay, $availableDate)) {
                        continue;
                    }

                    if ($perDayStudyTime < $availableHours) {
                        if ($planTask['suggestHours'] == 0 && $planTask['type'] == 'homework') {
                            $taskStartTime = $previewTaskInfo['taskEndTime'];//strtotime("-1 day", $taskStartTime);
                        }
                        $taskEndTime = $taskStartTime;
                    } 
                    else if ($perDayStudyTime == $availableHours) {
                        $taskEndTime = $taskStartTime;
                        $perDayStudyTime = $perDayStudyTime - $availableHours;
                        $dayLoop++;
                    } 
                    else {
                        $taskNeedDay = ceil($planTask['suggestHours'] / $availableHours) - 1;
                        $taskEndTime = strtotime("+{$taskNeedDay} day", $taskStartTime);
                        $perDayStudyTime = abs($perDayStudyTime - $availableHours * ($taskNeedDay + 1));

                        $dayLoop = $dayLoop + $taskNeedDay;
                    }

                    $taskInfo['taskStartTime'] = strtotime(date('Y-m-d',$taskStartTime).' 00:00:00') + $j;
                    $taskInfo['taskEndTime'] = strtotime(date('Y-m-d',$taskEndTime).' 23:59:59');

                    //$taskInfo['taskStartDate'] = date('Y-m-d H:i:s', $taskInfo['taskStartTime']);
                    //$taskInfo['taskEndDate'] = date('Y-m-d H:i:s', $taskInfo['taskEndTime']);
                    
                    if ($planTask['type'] == 'live') {
                        $liveLesson = $this->getCourseService()->getLesson($planTask['objectId']);
                        if ($liveLesson['endTime'] > time()) {
                            $taskInfo['taskStartTime'] = $liveLesson['startTime'];
                            $taskInfo['taskEndTime'] = $liveLesson['endTime'];
                        }
                    }

                    break;
                }*/
                list($dayLoop, $perDayStudyTime, $taskTimeInfo) = $this->_setTaskExecuteTime($dayLoop, $planTask, $planMember, $planStartTime, $perDayStudyTime, $lastTaskInfo);
                $taskTimeInfo['taskStartTime'] += $j;
                $taskInfo = array_merge($taskInfo, $taskTimeInfo);

                $taskInfo['targetId'] = $planTask['objectId'];
                $taskInfo['targetType'] = $planTask['type'];

                //根据课时学习状态获取任务完成状态
                $statusInfo = $this->_getTaskStatus($planTask, $userId, $plan);
                $taskInfo = array_merge($taskInfo, $info);

                if ($type == 'add') {
                    $this->getTaskService()->addTask($taskInfo);
                } else {
                    $conditions = array(
                        'userId' => $userId,
                        'taskType' => 'studyplan',
                        'targetId' => $taskInfo['targetId'],
                        'targetType' => $taskInfo['targetType']
                    );
                    $userTargetTask = $this->getTaskService()->getTaskByParams($conditions);
                    if (!$userTargetTask) {
                        $newTask = $this->getTaskService()->addTask($taskInfo);
                        $newTaskIds[] = $newTask['id'];
                    } else {
                        $newTaskIds[] = $userTargetTask['id'];
                    }
                }

                $lastTaskInfo = $taskInfo;
                unset($taskInfo['completedTime']);
                $j += 10;
            }
            //删除更新后不存在的任务
            if ($type == 'update') {
                $this->_deleteUnusedTasks($newTaskIds, $userId, $plan['id']);
            }

            $this->getTaskDao()->getConnection()->commit();
            return true;
        } catch (\Exception $e) {
            $this->getTaskDao()->getConnection()->rollback();
            throw $e;
        }
    }

    private function _setTaskExecuteTime($dayLoop, $planTask, $planMember, $planStartTime, $perDayStudyTime, $lastTaskInfo)
    {
        $availableHours = $planMember['metas']['availableHours'];
        $availableDate = $planMember['metas']['availableDate'];
        $targetDays = $planMember['metas']['targetWeeks'] * 7;

        for ($dayLoop; $dayLoop <= $targetDays; $dayLoop++) {

            $taskStartTime = $dayLoop == 0 ? $planStartTime : strtotime("+{$dayLoop} day", $planStartTime);

            $weekDay = date('w', $taskStartTime);
            if (!in_array($weekDay, $availableDate)) {
                continue;
            }

            if ($perDayStudyTime < $availableHours) {
                if ($planTask['suggestHours'] == 0 && $planTask['type'] == 'homework') {
                    $taskStartTime = $lastTaskInfo['taskEndTime'];
                }
                $taskEndTime = $taskStartTime;
            } 
            else if ($perDayStudyTime == $availableHours) {
                $taskEndTime = $taskStartTime;
                $perDayStudyTime = $perDayStudyTime - $availableHours;
                $dayLoop++;
            } 
            else {
                $taskNeedDay = ceil($planTask['suggestHours'] / $availableHours) - 1;
                $taskEndTime = strtotime("+{$taskNeedDay} day", $taskStartTime);
                $perDayStudyTime = abs($perDayStudyTime - $availableHours * ($taskNeedDay + 1));

                $dayLoop = $dayLoop + $taskNeedDay;
            }

            $timeInfo['taskStartTime'] = strtotime(date('Y-m-d',$taskStartTime).' 00:00:00');
            $timeInfo['taskEndTime'] = strtotime(date('Y-m-d',$taskEndTime).' 23:59:59');

            //$taskInfo['taskStartDate'] = date('Y-m-d H:i:s', $taskInfo['taskStartTime']);
            //$taskInfo['taskEndDate'] = date('Y-m-d H:i:s', $taskInfo['taskEndTime']);
            
            if ($planTask['type'] == 'live') {
                $liveLesson = $this->getCourseService()->getLesson($planTask['objectId']);
                if ($liveLesson['endTime'] > time()) {
                    $timeInfo['taskStartTime'] = $liveLesson['startTime'];
                    $timeInfo['taskEndTime'] = $liveLesson['endTime'];
                }
            }

            break;
        }

        return array($dayLoop, $perDayStudyTime, $timeInfo);
    }

    private function _getTaskStatus($planTask, $userId, $plan)
    {
        if ($planTask['type'] == 'homework') {
            $statusInfo = $this->_getUserHomeworkPassed($planTask['objectId'], $userId, $plan);
        } 
        else if ($planTask['type'] == 'testpaper') {
            $statusInfo = $this->_getUserTestpaperPassed($planTask['meta']['mediaId'], $userId, $plan);
        }
        else {
            //判断该课程是否已学过
            $lessonLearn = $this->getLessonLearnDao()->getLearnByUserIdAndLessonId($userId, $planTask['objectId']);
            if ($lessonLearn && $lessonLearn == 'finished') {
                $statusInfo['status'] = 'completed';
                $statusInfo['completedTime'] = $lessonLearn['finishedTime'];
            }
        }

        return $statusInfo;
    }

    private function _deleteUnusedTasks($newTaskIds, $userId, $batchId)
    {
        $tasks = $this->getTaskService()->findUserTasksByBatchIdAndTaskType($userId, $batchId, 'studyPlan'); 
        $allTaskIds = ArrayToolkit::column($tasks,'id');
        $diff = array_diff($allTaskIds, $newTaskIds);

        if ($diff) {
            foreach ($diff as $key => $taskId) {
                $this->getTaskService()->deleteTask($task['id']);
            }
        }
            
    }

    private function _getUserHomeworkPassed($homeworkId, $userId, $plan)
    {
        $taskInfo['required'] = $plan['requirePass'] ? 1 : 0 ;
        $homeworkconditions = array(
            'homeworkId' => $homeworkId,
            'userId' => $userId,
            'passedStatus' => array('good','excellent')
        );
        $homeworkPassed = $this->getHomeworkService()->searchResults($homeworkconditions, array('id','DESC'), 0, 1);

        if ($homeworkPassed) {
            $taskInfo['status'] = 'completed';
            $taskInfo['completedTime'] = $homeworkPassed[0]['checkedTime'];
        }

        return $taskInfo;

    }

    private function _getUserTestpaperPassed($testId, $userId, $plan)
    {
        $taskInfo['required'] = $plan['requirePass'] ? 1 : 0 ;
        $testConditions = array(
            'testId' => $testId,
            'userId' => $userId,
            'status' => 'finished',
            'passesStatus' => 'passed'
        );
        $testpaperPassed = $this->getTestpaperService()->searchTestpaperResults($testConditions, array('id','DESC'), 0, 1);

        if ($testpaperPassed) {
            $taskInfo['status'] = 'completed';
            $taskInfo['completedTime'] = $testpaperPassed[0]['checkedTime'];
        }

        return $taskInfo;
    }

    private function _canFinished($task, $targetObject)
    {
        $canFinished = true;
        if ($task['required'] && ($targetObject['type'] == 'homework' || $targetObject['type'] == 'testpaper')) {
            if ($targetObject['passedStatus'] == 'unpassed' || $targetObject['passedStatus'] == 'none') {
                $canFinished = false;
            }
        }
        
        return $canFinished;
    }

    protected function getTaskService()
    {
        return ServiceKernel::instance()->createService('Task.TaskService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getClassroomPlanService()
    {
        return ServiceKernel::instance()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanService');
    }

    protected function getClassroomPlanTaskService()
    {
        return ServiceKernel::instance()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanTaskService');
    }

    protected function getClassroomPlanMemberService()
    {
        return ServiceKernel::instance()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanMemberService');
    }

    protected function getHomeworkService()
    {
        return ServiceKernel::instance()->createService('Homework:Homework.HomeworkService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }

    protected function getTaskDao()
    {
        return ServiceKernel::instance()->createDao('Task.TaskDao');
    }

    protected function getLessonLearnDao()
    {
        return ServiceKernel::instance()->createDao('Course.LessonLearnDao');
    }

}