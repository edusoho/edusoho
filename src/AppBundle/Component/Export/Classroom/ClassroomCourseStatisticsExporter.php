<?php

namespace AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;

class ClassroomCourseStatisticsExporter extends Exporter
{
    //定义导出标题
    public function getTitles()
    {
        return [
            'admin.course_manage.statistics.data.name',
            'admin.course_manage.statistics.data.task_type',
            'admin.course_manage.statistics.data.video_length',
            'admin.course_manage.statistics.data.study_number',
            'admin.course_manage.statistics.data.finished_number',
            'admin.course_manage.statistics.data.task_sum_study_time',
            'admin.course_manage.statistics.data.average_study_time',
            'admin.course_manage.statistics.data.average_score',
        ];
    }

    //获得导出正文内容
    public function getContent($start, $limit)
    {
        $classroom = $this->getClassroomService()->getClassroom($this->conditions['classroomId']);
        $courseId = empty($this->conditions['courseId']) ? 0 : $this->conditions['courseId'];
        if (empty($courseId)) {
            $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
            $course = reset($classroomCourses);
            $courseId = $course['id'];
        }

        $tasks = empty($courseId) ? [] : $this->getTaskService()->searchTasksWithStatistics(['courseId' => $courseId], ['id' => 'ASC'], $start, $limit);
        $content = [];
        foreach ($tasks as $task) {
            $content[] = [
                $task['title'],
                $this->container->get('translator')->trans('course.activity.'.$task['type']),
                'video' === $task['type'] ? round($task['length'] / 60, 1) : '--',
                $task['studentNum'],
                $task['finishedNum'],
                $task['sumLearnedTime'],
                $task['avgLearnedTime'],
                'testpaper' === $task['type'] ? $task['score'] : '--',
            ];
        }

        return $content;
    }

    //下载权限判断
    public function canExport()
    {
        $user = $this->getUser();

        if ($user->hasPermission('admin_v2_classroom_statistics')) {
            return true;
        }

        return false;
    }

    //获得导出总条数
    public function getCount()
    {
        return $this->getTaskService()->countTasks($this->conditions);
    }

    //构建查询条件
    public function buildCondition($conditions)
    {
        return $conditions;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CoursePlanLearnDataDailyStatisticsService
     */
    protected function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->getBiz()->service('Visualization:CoursePlanLearnDataDailyStatisticsService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
