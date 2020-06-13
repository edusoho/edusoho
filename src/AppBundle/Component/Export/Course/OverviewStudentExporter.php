<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;

class OverviewStudentExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        try {
            $tryManageCourse = $this->getCourseService()->tryManageCourse($this->parameter['courseId']);
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
        $titles = [
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.finished_rate',
        ];
        $tasks = $this->getAllTaskByCourseId();

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
        $taskCount = $this->countTasksByCourseId();

        list($users, $tasks, $taskResults) = $this->getReportService()->getStudentDetail($course['id'], $userIds, $taskCount);

        $datas = [];

        $status = [
            'finish' => '已完成',
            'start' => '学习中',
        ];

        foreach ($members as $member) {
            $userTaskResults = !empty($taskResults[$member['userId']]) ? $taskResults[$member['userId']] : [];

            $user = $users[$member['userId']];
            $data = [];
            $data[] = $user['nickname']."\t";

            $learnProccess = (empty($member['learnedCompulsoryTaskNum']) || empty($course['compulsoryTaskNum'])) ? 0 : (int) ($member['learnedCompulsoryTaskNum'] * 100 / $course['compulsoryTaskNum']);
            $data[] = $learnProccess > 100 ? '100%' : $learnProccess.'%';

            foreach ($tasks as $task) {
                $taskResult = !empty($userTaskResults[$task['id']]) ? $userTaskResults[$task['id']] : [];
                $data[] = empty($taskResult) ? '未开始' : $status[$taskResult['status']];
            }

            $datas[] = $data;
        }

        return $datas;
    }

    private function getAllTaskByCourseId()
    {
        return $this->getTaskService()->searchTasks(
            [
                'courseId' => $this->parameter['courseId'],
                'isOptional' => 0,
                'status' => 'published',
            ],
            ['seq' => 'ASC'],
            0,
            PHP_INT_MAX
        );
    }

    private function countTasksByCourseId()
    {
        return $this->getTaskService()->countTasks(
            [
                'courseId' => $this->parameter['courseId'],
                'isOptional' => 0,
                'status' => 'published',
            ]
        );
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseId'] = $conditions['courseId'];
        $parameter['orderBy'] = $this->getReportService()->buildStudentDetailOrderBy($conditions);

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return $this->getReportService()->buildStudentDetailConditions($conditions, $conditions['courseId']);
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
