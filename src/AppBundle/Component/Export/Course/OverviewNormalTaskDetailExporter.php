<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewNormalTaskDetailExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getCourseMemberService()->countMembers($this->conditions);
    }

    public function getTitles()
    {
        return array('用户名', '加入学习时间' ,'完成任务时间', '任务学习时长(分)', '音视频观看时长(分)');
    }

    public function getContent($start, $limit)
    {
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $this->conditions,
            array('createdTime' => 'ASC'),
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $datas = array();

        foreach ($taskResults as $taskResult){
            $user = $users[$taskResult['userId']];

            $data = array();
            $data[] = $user['nickname'];
            $data[] = date("Y-m-d H:i:s", $taskResult['createdTime']);
            $data[] = empty($taskResult['finishedTime']) ? '-' : date("Y-m-d H:i:s", $taskResult['finishedTime']);
            $data[] = empty($taskResult['time']) ? '-' : round(($taskResult['time'] / 60), 1);
            $data[] = empty($taskResult['watchTime']) ? '-' : round(($taskResult['watchTime'] / 60), 1);

            $datas[] = $data;
        }

        return $datas;
    }


    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseTaskId'] = $conditions['courseTaskId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return ArrayToolkit::parts($conditions, array('courseTaskId'));
    }

    protected function getReportService()
    {
        return $this->getBiz()->service('Course:ReportService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }
}