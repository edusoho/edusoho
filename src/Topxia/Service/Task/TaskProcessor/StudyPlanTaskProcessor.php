<?php
namespace Topxia\Service\Task\TaskProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;
use Exception;

class StudyPlanProcessor extends BaseProcessor implements TaskProcessor
{

    public function getTask($taskId)
    {
        return $this->getTaskService()->getTask();
    }

    public function addTask(array $fields)
    {
        $classroomId = $fields['classroomId'];
        $userId = $fields['userId'];

        $plan = $this->getClassroomPlanService()->getPlanByClassroomId($classroomId);
        if (!$plan){
            throw new Exception("该学习计划不存在!");
        }

        $planTasks = $this->getClassroomPlanTaskService()->findTasksByPlanId($plan['id']);
        $planMember = $this->getClassroomPlanMemberService()->getPlanMemberByPlanId($plan['id'], $user['id']);

        if ($planTasks) {
            $taskCourseIds = array_unique(ArrayToolkit::column($planTasks, 'courseId'));

            $this->_prepareTaskAddInfo($planTasks, $userId, $taskCourseIds, $planMember);
        }

        return true;
    }

    public function updateTask($taskId, array $fields)
    {
        return $this->getTaskService()->updateTask($taskId, $fields);
    }
    
    protected function _prepareTaskAddInfo($planTasks, $userId, $courseIds, $planMember)
    {
        //获取我学过的课时
        $conditions = array(
            'userId' => $userId,
            'courseIds' => $courseIds,
            'status' => 'finished'
        );
        $userLearnedLessons = $this->getCourseService()->searchLearns($conditions, array(), 0, 1000);
        $userLearnedLessons = ArrayToolkit::index($userLearnedLessons,
            'lessonId');

        $taskInfo = array(
            'userId' => $userId,
            'taskType' => 'studyplan',
            'createdTime' => time()
        );

        
        $i = 0;
        $perDayStudyTime = 0;
        foreach ($planTasks as $key => $task) {

            $availableHours = $planMember['metas']['availableHours'];
            $availableDate = $planMember['metas']['availableDate'];
            $targetDays = $planMember['metas']['targetDays'];

            $taskInfo['title'] = $planTask['title'];
            $taskInfo['batchId'] = $plan['id'];
            $taskInfo['targetId'] = $task['objectId'];
            $taskInfo['targetType'] = $task['type'];
            $taskInfo['meta']['classroomId'] = $classroomId;
            $taskInfo['meta']['courseId'] = $planTask['courseId'];
            $taskInfo['meta']['phaseId'] = $planTask['phaseId'];

            $perDayStudyTime += $task['suggestHours'];
            //获取任务执行时间
            for ($i = $i; $i <= $targetDays; $i++) {
                $taskStartTime = $i == 0 ? time() : strtotime("+{$i} day");

                $weekDay = date('w', $taskStartTime);
                if (!in_array($weekDay, $availableDate)) {
                    continue;
                }

                if ($perDayStudyTime <= $availableHours) {
                    $taskEndTime = $taskStartTime;
                } else {
                    $taskNeedDay = ceil($task['suggestHours'] / $availableHours);
                    $taskEndTime = strtotime("+{$taskNeedDay} day", $taskStartTime);
                    $perDayStudyTime = $perDayStudyTime - $availableHours;

                    $i--;
                    continue;
                }

                $taskInfo['taskStartTime'] = strtotime(date('Y-m-d',$taskStartTime).' 00:00:00');
                $taskInfo['taskEndTime'] = strtotime(date('Y-m-d',$taskEndTime).' 23:59:59');

                $taskInfo['taskStartDate'] = date('Y-m-d H:i:s', $taskInfo['taskStartTime']);
                $taskInfo['taskEndDate'] = date('Y-m-d H:i:s', $taskInfo['taskEndTime']);
                break;
            }
            
            $taskInfo['targetId'] = $planTask['objectId'];
            $taskInfo['targetType'] = $planTask['type'];
            if ($planTask['type'] != 'homework' && $planTask['type'] != 'testpaper') {
                if (in_array($planTask['objectId'], $userLearnedLessons)) {
                    $taskInfo['status'] = 'completed';
                    $taskInfo['completedTime'] = $userLearnedLessons[$planTask['objectId']]['finishedTime'];
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
print_r($taskInfo);
            //$this->getTaskService()->addTask($taskInfo);
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
            'homeworkId' => $planTask['objectId'],
            'userId' => $userId,
            'passedStatus' => array('good','excellent')
        );
        $homeworkPassed = $this->getHomeworkService()->searchResults($homeworkconditions, array(), 0, 1);
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
        $testpaperPassed = $this->getTestpaperService()->searchTestpaperResults($testConditions, array(), 0, 1);

        $taskInfo['status'] = 'completed';
        $taskInfo['completedTime'] = $homeworkPassed[0]['checkedTime'];

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
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanService');
    }

    protected function getClassroomPlanTaskService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanTaskService');
    }

    protected function getClassroomPlanMemberService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanMemberService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

}