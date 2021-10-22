<?php

namespace Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use PHPExcel_Exception;

class ClassroomLiveStatisticExporter extends BaseSheetAddStyleExporter
{
    public function getExportFileName()
    {
        $time = date('Y_m_d_H_i', time());

        return  "班级直播统计_{$time}.xls";
    }

    public function getSortedHeadingRow()
    {
        return [
            '课程名称' => 'courseTitle',
            '任务' => 'title',
            '直播开始时间' => 'startTime',
            '直播时长（分）' => 'length',
            '最大参与人数' => 'maxStudentNum',
            '进行状态' => 'status',
        ];
    }

    public function buildExportSheetData($params)
    {
        $sheetIndex = 0;
        try {
            $this->PHPExcel->createSheet($sheetIndex);
            $sheet = $this->PHPExcel->setActiveSheetIndex($sheetIndex);
            $this->setDefaultRowHeight();
            $data = $this->buildData($params);
            $this->setBorders('A1:E1');
            $this->setSize(['A1'], 14);
            $this->setBold(['A1:E1']);
            $this->setVerticalCenter(['A1']);
            $sheet->setTitle('直播统计');
            $this->setWidth(['A', 'B', 'C', 'D', 'E']);
            $this->setSheetCellValue($sheet, $data);
        } catch (PHPExcel_Exception $e) {
            throw $e;
        }
    }

    protected function buildData($params)
    {
        $courses = $this->getClassroomService()->findByClassroomId($params['classroomId']);
        $courseIds = empty($courses) ? [-1] : ArrayToolkit::column($courses, 'courseId');
        $taskConditions = [
            'courseIds' => empty($params['courseId']) ? $courseIds : array_intersect($courseIds, [$params['courseId']]),
            'type' => 'live',
            'titleLike' => $params['title'],
            'status' => 'published',
        ];

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            ['seq' => 'ASC'],
            0,
            PHP_INT_MAX,
            ['title', 'startTime', 'endTime', 'length', 'courseId']
        );
        $courseIds = ArrayToolkit::column($liveTasks, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');

        foreach ($liveTasks as &$liveTask) {
            $course = $courses[$liveTask['courseId']];
            $liveTask['courseTitle'] = empty($course['title']) ? $course['courseSetTitle'] : $course['title'];
            $liveTask['startTime'] = date('Y-m-d H:i', $liveTask['startTime']);
            $liveTask['maxStudentNum'] = empty($course['maxStudentNum']) ? '无限制' : $course['maxStudentNum'];
            $liveTask['status'] = $liveTask['startTime'] > time() ? $this->trans('course.live_statistics.live_coming') : ($liveTask['endTime'] < time() ? $this->trans('course.live_statistics.live_finished') : $this->trans('course.live_statistics.live_playing'));
        }

        return $liveTasks;
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return LiveCloudStatisticsServiceImpl
     */
    protected function getLiveStatisticsService()
    {
        return $this->createService('LiveStatistics:LiveCloudStatisticsService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
