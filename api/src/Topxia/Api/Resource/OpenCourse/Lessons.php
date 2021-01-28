<?php

namespace Topxia\Api\Resource\OpenCourse;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;
use Biz\OpenCourse\Service\Impl\OpenCourseServiceImpl;

class Lessons extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = $request->query->all();
        $start      = $request->query->get('start', 0);
        $limit      = $request->query->get('limit', 20);

        if (isset($conditions['cursor'])) {
            $conditions['status']         = 'published';
            $conditions['updatedTime_GE'] = $conditions['cursor'];
            $lessons                      = $this->getOpenCourseService()->searchLessons($conditions, array('createdTime'=> 'ASC'), $start, $limit);
            $next                         = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $lessons);
            return $this->wrap($this->filter($lessons), $next);
        } else {
            $total   = $this->getOpenCourseService()->countLessons($conditions);
            $start   = $start == -1 ? rand(0, $total - 1) : $start;
            $lessons = $this->getOpenCourseService()->searchLessons($conditions, array('createdTime'=> 'ASC'), $start, $limit);
            return $this->wrap($this->filter($lessons), $total);
        }
    }

    public function filter($res)
    {
        return $this->multicallFilter('OpenCourse/Lesson', $res);
    }

    protected function multicallFilter($name, $res)
    {
        foreach ($res as $key => $one) {
            $res[$key]            = $this->callFilter($name, $one);
            $res[$key]['body']    = '';
            $res[$key]['content'] = '';
        }
        return $res;
    }

    /**
     * @return OpenCourseServiceImpl
     */
    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse:OpenCourseService');
    }
}
