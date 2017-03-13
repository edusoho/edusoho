<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Callback\Resource\BaseResource;
use AppBundle\Common\ArrayToolkit;

/**
 * 兼容模式，对应course_task.
 */
class Lessons extends BaseResource
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $conditions['status'] = 'published';
        $conditions['updatedTime_GE'] = $conditions['cursor'];
        $courseTasks = $this->getCourseTaskService()->searchTasks($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $courseTasks = $this->build($courseTasks);
        $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $courseTasks);

        return $this->wrap($this->filter($courseTasks), $next);
    }

    public function build($courseTasks)
    {
        $courseTasks = $this->buildActivity($courseTasks);

        return $courseTasks;
    }

    protected function buildActivity($courseTasks)
    {
        $activityIds = ArrayToolkit::column($courseTasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');

        foreach ($courseTasks as &$task) {
            $task['activity'] = $activities[$task['activityId']];
        }

        return $courseTasks;
    }

    public function filter($res)
    {
        return $this->multicallFilter('cloud_search_lesson', $res);
    }

    public function simplify($res)
    {
        return $this->multicallSimplify('cloud_search_lesson', $res);
    }

    protected function multicallFilter($name, array $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
        }

        return $res;
    }

    /**
     * @return \Biz\Task\Service\TaskService
     */
    protected function getCourseTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return \Biz\Activity\Service\ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
