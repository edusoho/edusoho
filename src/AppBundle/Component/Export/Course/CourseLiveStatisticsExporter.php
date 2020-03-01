<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;

class CourseLiveStatisticsExporter extends Exporter
{
    public function buildCondition($conditions)
    {
        return array(
            'courseId' => $conditions['courseId'],
            'fromCourseSetId' => $conditions['courseSetId'],
            'type' => 'live',
            'titleLike' => empty($conditions['title']) ? '' : $conditions['title'],
            'status' => 'published',
        );
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseId'] = $conditions['courseId'];
        $parameter['course'] = $this->getCourseService()->tryManageCourse($conditions['courseId']);

        return $parameter;
    }

    public function getTitles()
    {
        return array(
            'course.task',
            'course.live_statistics.live_start_time',
            'course.live_statistics.live_time_long',
            'course.live_statistics.max_participate_count',
            'course.live_statistics.live_status',
            'course.live_statistics.check_in_status',
            'course.live_statistics.average_learn_time',
        );
    }

    public function canExport()
    {
        try {
            $tryManageCourse = $this->getCourseService()->tryManageCourse($this->parameter['courseId']);
        } catch (\Exception $e) {
            return false;
        }

        return $this->getUser()->isAdmin() || !empty($tryManageCourse);
    }

    public function getCount()
    {
        return $this->getTaskService()->countTasks($this->conditions);
    }

    public function getContent($start, $limit)
    {
        $tasks = $this->getTaskService()->searchTasks($this->conditions, array('seq' => 'ASC'), $start, $limit);

        $activities = $this->getActivityService()->findActivities(ArrayToolkit::column($tasks, 'activityId'), true);

        $activityLives = array();
        array_filter($activities, function ($value) use (&$activityLives) {
            $activityLives[$value['id']] = $value['ext']['liveId'];
        });

        $checkinStatistics = $this->getLiveStatisticsService()->findCheckinStatisticsByLiveIds(array_values($activityLives));
        $visitorStatistics = $this->getLiveStatisticsService()->findVisitorStatisticsByLiveIds(array_values($activityLives));

        $translator = $this->container->get('translator');

        $data = array();
        foreach ($tasks as $task) {
            $liveId = empty($activityLives[$task['activityId']]) ? 0 : $activityLives[$task['activityId']];
            $checkinCount = empty($checkinStatistics[$liveId]['data']['detail']) ? 0 : count($checkinStatistics[$liveId]['data']['detail']);
            $totalLearnTime = empty($visitorStatistics[$liveId]['data']['totalLearnTime']) ? 0 : $visitorStatistics[$liveId]['data']['totalLearnTime'];
            $status = $task['endTime'] < time() ? $translator->trans('course.live_statistics.live_finished') : ($task['startTime'] > time() ? $translator->trans('course.live_statistics.live_coming') : $translator->trans('course.live_statistics.live_playing'));

            $data[] = array(
                $task['title'],
                date('Y-m-d H:i:s', $task['startTime']),
                $task['length'],
                $this->parameter['course']['maxStudentNum'],
                $status,
                $checkinCount.'/'.$this->parameter['course']['studentNum']."\t",
                empty($this->parameter['course']['studentNum']) ? 0 : ceil($totalLearnTime / 60 / $this->parameter['course']['studentNum']),
            );
        }

        return $data;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->getBiz()->service('Live:LiveStatisticsService');
    }
}
