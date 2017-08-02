<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewStudentExporter extends Exporter
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
        $titles = array('学员详情', '联系方式', '完成度');
        $tasks = $this->getTaskService()->searchTasks(
            array(
                'courseId' => $this->parameter['courseId'],
                'isOptional' => 0,
                'status' => 'published',
            ),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $taskTitles = ArrayToolkit::column($tasks, 'title');

        return array_merge($titles, $taskTitles);
    }

    public function getContent($start, $limit)
    {
        $course = $this->getCourseService()->getCourse($this->parameter['courseId']);

        $members = $this->getCourseMemberService()->searchMembers(
            $this->conditions,
            $this->parameter['orderBy'],
            $start,
            $limit
        );

        $userIds = ArrayToolkit::column($members, 'userId');

        list($users, $tasks, $taskResults) = $this->getReportService()->getStudentDetail($course['id'], $userIds);

        foreach ($users as $user) {

        }

    }

    public function buildCondition($conditions)
    {
        $courseId = $conditions['courseId'];
        $orderBy = $this->getReportService()->buildStudentDetailOrderBy($conditions);
        $conditions = $this->getReportService()->buildStudentDetailConditions($conditions,  $this->courseId);
        return array($conditions, array(
            'courseId' => $courseId,
            'orderBy' => $orderBy
        ));
    }

    protected function getReportService()
    {
        return $this->createService('Course:ReportService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}