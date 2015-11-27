<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class CourseLessons extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $conditions = ArrayToolkit::parts($request->query->all(), array());

        $sort = $request->query->get('sort', 'published');
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $total = $this->getCourseService()->searchLessonCount($conditions);
        $start = $start == -1 ? rand(0, $total - 1) : $start;
        $courselessons = $this->getCourseService()->searchLessons($conditions, array('createdTime','DESC'), $start, $limit);
        return $this->wrap($this->filter($courselessons), $total);
    }

    public function filter(&$res)
    {
        return $this->multicallFilter('CourseLesson', $res);
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
