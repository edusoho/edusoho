<?php

namespace Biz\Exporter;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\LiveStatistics\Service\Impl\LiveCloudStatisticsServiceImpl;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use PHPExcel_Exception;

class CourseLiveStatisticExporter extends BaseSheetAddStyleExporter
{
    public function getExportFileName()
    {
        $time = date('Y_m_d_H_i', time());

        return  "课程直播统计_{$time}.xls";
    }

    public function getSortedHeadingRow()
    {
        return [
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
        $course = $this->getCourseService()->getCourse($params['courseId']);
        $taskConditions = [
            'courseId' => $params['courseId'],
            'type' => 'live',
            'titleLike' => $params['title'],
            'status' => 'published',
        ];

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            ['seq' => 'ASC'],
            0,
            PHP_INT_MAX,
            ['title', 'startTime', 'endTime', 'length']
        );
        foreach ($liveTasks as &$liveTask) {
            $liveTask['startTime'] = date('Y-m-d H:i', $course['startTime']);
            $liveTask['maxStudentNum'] = empty($course['maxStudentNum']) ? '无限制' : $course['maxStudentNum'];
            $liveTask['status'] = $liveTask['startTime'] > time() ? $this->trans('course.live_statistics.live_coming') : ($liveTask['endTime'] < time() ? $this->trans('course.live_statistics.live_finished') : $this->trans('course.live_statistics.live_playing'));
        }

        return $liveTasks;
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
