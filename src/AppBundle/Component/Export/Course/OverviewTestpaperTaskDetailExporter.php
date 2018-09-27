<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewTestpaperTaskDetailExporter extends Exporter
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
        return $this->getTaskResultService()->countTaskResults($this->conditions);
    }

    public function getTitles()
    {
        return array(
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.createdTime',
            'course.task.finish_time',
            'task.learn_data_detail.testpaper_firstUsedTime',
            'task.learn_data_detail.testpaper_firstScore',
            'task.learn_data_detail.testpaper_maxScore',
        );
    }

    public function getContent($start, $limit)
    {
        $task = $this->getTaskService()->getTask($this->parameter['courseTaskId']);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
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
