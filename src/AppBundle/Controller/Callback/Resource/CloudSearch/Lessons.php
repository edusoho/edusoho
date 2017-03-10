<?php

namespace AppBundle\Controller\Callback\Resource\CloudSearch;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Callback\Resource\BaseResource;

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
        $lessons = $this->getCourseTaskService()->searchTasks($conditions, array('updatedTime' => 'ASC'), $start, $limit);
        $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $lessons);

        return $this->wrap($this->filter($lessons), $next);
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
            $res[$key]['body'] = '';
            $res[$key]['content'] = '';
        }

        return $res;
    }

    protected function getCourseTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
