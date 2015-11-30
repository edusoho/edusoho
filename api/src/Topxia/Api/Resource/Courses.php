<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Courses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        if (isset($conditions['cursor'])) {
            $conditions['updatedTime_GE'] = (int)$conditions['cursor'];
            $courses = $this->getCourseService()->searchCourses($conditions, array('updatedTime', 'ASC'), $start, $limit);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $courses);
            return $this->wrap($this->filter($courses), $next);
        } else {
            $total = $this->getCourseService()->searchCourseCount($conditions);
            $users = $this->getCourseService()->searchCourses($conditions, array('createdTime','DESC'), $start, $limit);
            return $this->wrap($this->filter($users), $total);
        }

    }

    public function filter(&$res)
    {
        return $this->multicallFilter('Course', $res);
    }

    protected function multicallFilter($name, &$res)
    {
        foreach ($res as &$one) {
            $this->callFilter($name, $one);
        }
        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
