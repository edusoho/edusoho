<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewNormalTaskDetailExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        $task = $this->getTaskService()->getTask($this->parameter['courseTaskId']);

        try {
            $tryManageCourse = $this->getCourseService()->tryManageCourse($task['courseId']);
        } catch (\Exception $e) {
            return false;
        }

        return $user->isAdmin() || !empty($tryManageCourse);
    }

    public function getCount()
    {
        return $this->getCourseMemberService()->countMembers($this->conditions);
    }

    public function getTitles()
    {
        return [
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.createdTime',
            'task.learn_data_detail.finishedTime',
            'task.learn_data_detail.learnTime',
            'task.learn_data_detail.video_and_audio_learnTime',
        ];
    }

    public function getContent($start, $limit)
    {
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $this->conditions,
            ['createdTime' => 'ASC'],
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $datas = [];

        foreach ($taskResults as $taskResult) {
            $user = $users[$taskResult['userId']];

            $data = [];
            $data[] = $user['nickname']."\t";
            $data[] = date('Y-m-d H:i:s', $taskResult['createdTime']);
            $data[] = empty($taskResult['finishedTime']) ? '-' : date('Y-m-d H:i:s', $taskResult['finishedTime']);
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
        return ArrayToolkit::parts($conditions, ['courseTaskId']);
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
