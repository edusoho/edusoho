<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

class Lessons extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        if (isset($conditions['cursor'])) {
            $conditions['status'] = 'published';
            $conditions['updatedTime_GE'] = $conditions['cursor'];

            $tasks = $this->getTaskService()->searchTasks($conditions, array('updatedTime' => 'ASC'), $start, $limit);
            $tasks = $this->getCourseService()->convertTasks($tasks, array());

            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $tasks);

            return $this->wrap($this->filter($tasks), $next);
        } else {
            $total = $this->getTaskService()->countTasks($conditions);

            $start = $start == -1 ? rand(0, $total - 1) : $start;

            $tasks = $this->getTaskService()->searchTasks($conditions, array('createdTime' => 'ASC'), $start, $limit);

            if (!empty($conditions['courseId'])) {
                $course = $this->getCourseService()->getCourse($conditions['courseId']);
                $tasks = $this->getCourseService()->convertTasks($tasks, $course);
            }

            return $this->wrap($this->filter($tasks), $total);
        }
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course/Lesson', $res);
    }

    public function simplify($res)
    {
        return $this->multicallSimplify('Course/Lesson', $res);
    }

    protected function multicallFilter($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key] = $this->callFilter($name, $one);
            $res[$key]['body'] = '';
            $res[$key]['content'] = '';
        }
        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
