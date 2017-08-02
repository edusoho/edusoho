<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewTestpaperTaskDetailExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getTaskResultService()->countTaskResults($this->conditions);
    }

    public function getTitles()
    {
        return array('用户名', '加入学习时间', '完成任务时间', '首次考试用时(分)', '首次考试得分', '最高分');
    }

    public function getContent($start, $limit)
    {
        $activity = $this->getActivityService()->getActivity($this->parameter['courseTaskId'], true);
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($activity['ext']['mediaId'], $activity['mediaType']);

        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $this->conditions,
            array('createdTime' => 'ASC'),
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $testpaperResults = $this->getTestpaperService()->findTestResultsByTestpaperIdAndUserIds($userIds, $testpaper['id']);

        $datas = array();

        foreach ($taskResults as $taskResult) {
            $user = $users[$taskResult['userId']];
            $testpaperResult = empty($testpaperResults[$taskResult['userId']]) ? array() : $testpaperResults[$taskResult['userId']];
            $data = array();
            $data[] = $user['nickname'];
            $data[] = empty($taskResult['createdTime']) ? '-' : date('Y-m-d H:i:s', $taskResult['createdTime']);
            $data[] = empty($taskResult['finishedTime']) ? '-' : date('Y-m-d H:i:s', $taskResult['finishedTime']);
            $data[] = empty($testpaperResult['usedTime']) ? '-' : $testpaperResult['usedTime'];
            $data[] = empty($testpaperResult['firstScore']) ? '-' : $testpaperResult['firstScore'];
            $data[] = empty($testpaperResult['maxScore']) ? '-' : $testpaperResult['maxScore'];

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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
