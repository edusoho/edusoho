<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Lessons extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);

        if (isset($conditions['cursor'])) {
            $conditions['status'] = 'published';
            $conditions['updatedTime_GE'] = $conditions['cursor'];
            $lessons = $this->getCourseService()->searchLessons($conditions, array('updatedTime','ASC'), $start, $limit);
            $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $lessons);
            return $this->wrap($this->filter($lessons), $next);
        } else {
            $total = $this->getCourseService()->searchLessonCount($conditions);
            $start = $start == -1 ? rand(0, $total - 1) : $start;
            $lessons = $this->getCourseService()->searchLessons($conditions, array('createdTime','ASC'), $start, $limit);
            return $this->wrap($this->filter($lessons), $total);
        }

    }

    public function filter(&$res)
    {
        return $this->multicallFilter('Lesson', $res);
    }

    protected function multicallFilter($name, &$res)
    {
        foreach ($res as &$one) {
            $this->callFilter($name, $one);
            $one['body'] = '';
        }
        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
