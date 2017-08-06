<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewTaskExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        $tryManageCourse = $this->getCourseService()->tryManageCourse($this->parameter['courseId']);

        return $user->isAdmin() || $tryManageCourse;
    }

    public function getCount()
    {
        return $this->getTaskService()->countTasks($this->conditions);
    }

    public function getTitles()
    {
        return array('任务详情', '已完成(人)', '未完成(人)', '未开始(人)', '完成度');
    }

    public function getContent($start, $limit)
    {
        $course = $this->getCourseService()->getCourse($this->parameter['courseId']);
        $tasks = $this->getTaskservice()->searchTasks(
            $this->conditions,
            array('seq' => 'asc'),
            $start,
            $limit
        );

        $tasks = $this->getReportService()->getCourseTaskLearnData($tasks, $course['id']);
        $datas = array();
        foreach ($tasks as $task) {
            $data = array();
            $data[] = $task['title'];
            $data[] = $task['finishedNum'];
            $data[] = $task['notStartedNum'];
            $data[] = $task['learnNum'];
            $data[] = $task['rate'];

            $datas[] = $data;
        }

        return $datas;
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseId'] = $conditions['courseId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return ArrayToolkit::parts($conditions, array('titleLike', 'courseId'));
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
}
