<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

/**
 * @todo  
 */
class UserClassroomPlanTaskNumInfoDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取用户今日任务
     *
     * 可传入的参数：
     *   classroomId   必需 班级ID
     *   userId        必需　用户ID
     * 
     * @param  array $arguments 参数
     * @return array 用户班级学习计划任务数量信息
     */
    public function getData(array $arguments)
    {   
        $this->checkArguments($arguments);
        $classroomPlan = $this->getClassroomPlanService()->getPlanByClassroomId($arguments['classroomId']);
        if (!$classroomPlan) {
            return array();
        }

        $planMember = $this->getClassroomPlanMemberService()->getPlanMemberByPlanId($classroomPlan['id'], $arguments['userId']);
        if (!$planMember) {
            return array();
        }
        
        $userDoneInfo = array();

        $userDoneInfo['homeworkDoneNum'] = $this->getTaskService()->searchTaskCount(array(
            'userId' => $arguments['userId'],
            'batchId' => $classroomPlan['id'],
            'taskType' => 'studyplan',
            'status' => 'completed',
            'targetType' => 'homework'
        ));
        $userDoneInfo['homeworkTaskNum'] = $this->getTaskService()->searchTaskCount(array(
            'userId' => $arguments['userId'],
            'batchId' => $classroomPlan['id'],
            'taskType' => 'studyplan',
            'targetType' => 'homework'
        ));

        $userDoneInfo['testpaperDoneNum'] = $this->getTaskService()->searchTaskCount(array(
            'userId' => $arguments['userId'],
            'batchId' => $classroomPlan['id'],
            'taskType' => 'studyplan',
            'status' => 'completed',
            'targetType' => 'testpaper'
        ));
        $userDoneInfo['testpaperTaskNum'] = $this->getTaskService()->searchTaskCount(array(
            'userId' => $arguments['userId'],
            'batchId' => $classroomPlan['id'],
            'taskType' => 'studyplan',
            'targetType' => 'testpaper'
        ));

        $userDoneInfo['allDoneNum'] = $this->getTaskService()->searchTaskCount(array(
            'userId' => $arguments['userId'],
            'batchId' => $classroomPlan['id'],
            'taskType' => 'studyplan',
            'status' => 'completed',
        ));
        $userDoneInfo['tasksNum'] = $this->getTaskService()->searchTaskCount(array(
            'userId' => $arguments['userId'],
            'batchId' => $classroomPlan['id'],
            'taskType' => 'studyplan',
        ));

        $userDoneInfo['lessonDoneNum'] = $userDoneInfo['allDoneNum'] - $userDoneInfo['homeworkDoneNum'] - $userDoneInfo['testpaperDoneNum'];
        $userDoneInfo['lessonTaskNum'] = $userDoneInfo['tasksNum'] - $userDoneInfo['homeworkTaskNum'] - $userDoneInfo['testpaperTaskNum'];

        return $userDoneInfo;
    }

    protected function checkArguments(array $arguments)
    {
        if (empty($arguments['userId'])) {
            throw new \InvalidArgumentException("userId参数缺失");
        }

        if (empty($arguments['classroomId'])) {
            throw new \InvalidArgumentException("classroomId参数缺失");
        }
    }


    protected function getClassroomPlanService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanService');
    }

    protected function getClassroomPlanMemberService()
    {
        return $this->getServiceKernel()->createService('ClassroomPlan:ClassroomPlan.ClassroomPlanMemberService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task.TaskService');
    }
}
