<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskService;

class MeLiveSchedule extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $memberCourses = $this->getCourseMemberService()->findStudentMemberByUserId($this->getCurrentUser()->getId());

        if (empty($memberCourses)) {
            return array();
        }

        $courseIds = ArrayToolkit::column($memberCourses, 'courseId');

        $conditions = array(
            'type' => 'live',
            'courseIds' => $courseIds,
            'status' => 'published',
            'startTime_GE' => $request->query->get('startTime'),
            'startTime_LE' => $request->query->get('endTime'),
        );

        $total = $this->getTaskService()->countTasks($conditions);

        if (empty($total)) {
            return array();
        }

        $lives = $this->getTaskService()->searchTasks($conditions, array('startTime' => 'DESC'), 0, $total);

        $days = array();
        foreach ($lives as &$live) {
            $day = strtotime(date('Y-m-d', $live['startTime']));
            $days[$day]['date'] = strtotime('00:00:00', $live['startTime']);
            $days[$day]['count'] = empty($days[$day]['count']) ? 1 : ($days[$day]['count'] + 1);
        }

        return array_values($days);
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
