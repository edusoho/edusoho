<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseLessons extends BaseProvider
{
    public function get(Request $request)
    {
        $conditions = $request->query->all();
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        $conditions['status'] = 'published';
        $conditions['updatedTime_GE'] = $conditions['cursor'];
        $lessons = $this->getOpenCourseService()->searchLessons($conditions, array('createdTime' => 'ASC'), $start, $limit);
        $next = $this->nextCursorPaging($conditions['cursor'], $start, $limit, $lessons);

        return $this->wrap($this->filter($lessons), $next);
    }

    public function filter($res)
    {
        return $this->multicallFilter('open_course_lesson', $res);
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

    /**
     * @return Biz\OpenCourse\Service\OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
