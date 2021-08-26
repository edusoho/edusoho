<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Course\Service\CourseService;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Task\Service\TaskService;

class LiveStatisticsVisitorListExporter extends Exporter
{
    public function buildCondition($conditions)
    {
        return [
            'liveId' => $conditions['liveId'],
            'courseId' => $conditions['courseId'],
            'taskId' => $conditions['taskId'],
        ];
    }

    public function getTitles()
    {
        return [
            'user.fields.username_label',
            'user.fields.mobile_simple_label',
            'user.fields.email_label',
            'course.live_statistics.first_join',
            'course.live_statistics.last_leave',
            'course.live_statistics.learn_time',
        ];
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
        $statistics = $this->getLiveStatisticsService()->getVisitorStatisticsByLiveId($this->conditions['liveId']);

        return empty($statistics['data']['detail']) ? 0 : count($statistics['data']['detail']);
    }

    public function getContent($start, $limit)
    {
        $statistics = $this->getLiveStatisticsService()->getVisitorStatisticsByLiveId($this->conditions['liveId']);

        $statistics = array_slice($statistics['data']['detail'], $start, $limit);

        $data = [];

        $userIds = ArrayToolkit::column($statistics, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);

        foreach ($statistics as $user) {
            $data[] = [
                $user['nickname'],
                $profiles[$user['userId']]['mobile'] ?: '',
                $users[$user['userId']]['email'] ?: '',
                date('Y-m-d H:i:s', $user['firstJoin']),
                date('Y-m-d H:i:s', $user['lastLeave']),
                ceil($user['learnTime'] / 60),
            ];
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
