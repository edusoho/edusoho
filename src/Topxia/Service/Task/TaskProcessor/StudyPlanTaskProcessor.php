<?php
namespace Topxia\Service\Task\TaskProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Exception;

class StudyPlanTaskProcessor implements TaskProcessor
{
    public function addTask(array $fields)
    {
        $classroomId = $fields['classroomId'];
        $userId = $fields['userId'];

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

    public function finishTask(array $targetObject, $userId)
    {
        $getTask = $this->getTaskService()->getActiveTaskBy($userId, 'studyplan', $targetObject['id'], $targetObject['type']);

        if ($getTask) {
            $updateInfo = array('status'=>'completed', 'completedTime'=>time());

            return $this->getTaskService()->updateTask($getTask['id'], $updateInfo);
        }

        return array();
    }
    
    protected function _prepareTaskAddInfo($planTasks, $plan, $userId, $courseIds, $planMember)
    {
        //获取我学过的课时
        $conditions = array(
            'userId' => $userId,
            'courseIds' => $courseIds,
            'status' => 'finished'
        );
        $userLearnedLessons = $this->getCourseService()->searchLearns($conditions, array('id','DESC'), 0, 1000);
        $userLearnedLessons = ArrayToolkit::index($userLearnedLessons,
            'lessonId');

        $taskInfo = array(
            'userId' => $userId,
            'taskType' => 'studyplan',
            'createdTime' => time()
        );


        $i = 0;
        $perDayStudyTime = 0;
        foreach ($planTasks as $key => $planTask) {

            $availableHours = $planMember['metas']['availableHours'];
            $availableDate = $planMember['metas']['availableDate'];
            $targetDays = $planMember['metas']['targetDays'];

            $taskInfo['title'] = $planTask['title'];
            $taskInfo['batchId'] = $plan['id'];
            $taskInfo['targetId'] = $planTask['objectId'];
            $taskInfo['targetType'] = $planTask['type'];
            $taskInfo['required'] = 0;
            $taskInfo['meta']['classroomId'] = $plan['classroomId'];
            $taskInfo['meta']['courseId'] = $planTask['courseId'];
            $taskInfo['meta']['phaseId'] = $planTask['phaseId'];

            $perDayStudyTime += $planTask['suggestHours'];
            
            //获取任务执行时间
            for ($i = $i; $i <= $targetDays; $i++) {
                
                $taskStartTime = $i == 0 ? time() : strtotime("+{$i} day");

                $weekDay = date('w', $taskStartTime);
                if (!in_array($weekDay, $availableDate)) {
                    continue;
                }

                if ($perDayStudyTime < $availableHours) {
                    $taskEndTime = $taskStartTime;
                } 
                else if ($perDayStudyTime == $availableHours) {
                    $taskEndTime = $taskStartTime;
                    $perDayStudyTime = $perDayStudyTime - $availableHours;
                    $i++;
                } 
                else {
                    $taskNeedDay = ceil($planTask['suggestHours'] / $availableHours);
                    $taskEndTime = strtotime("+{$taskNeedDay} day", $taskStartTime);
                    $perDayStudyTime = $perDayStudyTime - $availableHours;

                    $i++;
                }

                $taskInfo['taskStartTime'] = strtotime(date('Y-m-d',$taskStartTime).' 00:00:00');
                $taskInfo['taskEndTime'] = strtotime(date('Y-m-d',$taskEndTime).' 23:59:59');

                /*$taskInfo['taskStartDate'] = date('Y-m-d H:i:s', $taskInfo['taskStartTime']);
                $taskInfo['taskEndDate'] = date('Y-m-d H:i:s', $taskInfo['taskEndTime']);*/
                
                break;
            }
            
            $taskInfo['targetId'] = $planTask['objectId'];
            $taskInfo['targetType'] = $planTask['type'];
            if ($planTask['type'] != 'homework' && $planTask['type'] != 'testpaper') {
                if ($userLearnedLessons && $userLearnedLessons[$planTask['objectId']]) {
                    $taskInfo['status'] = 'completed';
                    $taskInfo['completedTime'] = $userLearnedLessons[$planTask['objectId']]['finishedTime'];
                }

                if ($planTask['type'] == 'live') {
                    $liveLesson = $this->getCourseService()->getLesson($planTask['objectId']);
                    $taskInfo['taskStartTime'] = $liveLesson['startTime'];
                    $taskInfo['taskEndTime'] = $liveLesson['endTime'];
                }
            } 
            else if ($planTask['type'] == 'homework') {
                $info = $this->_getUserHomeworkPassed($planTask['objectId'], $userId);
                $taskInfo = array_merge($taskInfo, $info);
            } 
            else if ($planTask['type'] == 'testpaper') {
                $info = $this->_getUserTestpaperPassed($planTask['objectId'], $userId);
                $taskInfo = array_merge($taskInfo, $info);
            }

            $this->getTaskService()->addTask($taskInfo);
        }

        return true;
    }

    protected function _getTaskTime()
    {

    }

    protected function _getUserHomeworkPassed($homeworkId, $userId)
    {
        $taskInfo['required'] = 1;
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

    protected function _getUserTestpaperPassed($testId, $userId)
    {
        $taskInfo['required'] = 1;
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

}