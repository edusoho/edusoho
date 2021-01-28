<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Component\Export\Exporter;
use Biz\Course\Service\CourseService;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;

class LiveStatisticsCheckinListExporter extends Exporter
{
    public function buildCondition($conditions)
    {
        return array(
            'liveId' => $conditions['liveId'],
            'courseId' => $conditions['courseId'],
            'taskId' => $conditions['taskId'],
        );
    }

    public function getTitles()
    {
        return array(
            'user.fields.username_label',
            'course.live_statistics.checkin_status',
        );
    }

    public function canExport()
    {
        try {
            $tryManageCourse = $this->getCourseService()->tryManageCourse($this->conditions['courseId']);
        } catch (\Exception $e) {
            return false;
        }

        return $this->getUser()->isAdmin() || !empty($tryManageCourse);
    }

    public function getCount()
    {
        $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($this->conditions['liveId']);

        return empty($statistics['data']['detail']) ? 0 : count($statistics['data']['detail']);
    }

    public function getContent($start, $limit)
    {
        $statistics = $this->getLiveStatisticsService()->getCheckinStatisticsByLiveId($this->conditions['liveId']);

        $statistics = array_slice($statistics['data']['detail'], $start, $limit);
        $translator = $this->container->get('translator');

        $data = array();

        foreach ($statistics as $user) {
            $data[] = array(
                $user['nickname'],
                $user['checkin'] ? $translator->trans('course.live_statistics.checkin_status.checked') : $translator->trans('course.live_statistics.checkin_status.not_checked'),
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
     * @return LiveStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->getBiz()->service('Live:LiveStatisticsService');
    }
}
