<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

/**
 * 为了兼容老版本APP的获取课时路由：/course/{courseId}/lessons.
 */
class LessonsToBeDelete extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $conditions = $request->query->all();

        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 20);

        if (empty($conditions['courseId'])) {
            $conditions['courseId'] = $courseId;
        }

        $total = $this->getCourseService()->searchLessonCount($conditions);
        $start = $start == -1 ? rand(0, $total - 1) : $start;
        $lessons = $this->getCourseService()->searchLessons($conditions, array('createdTime' => 'ASC'), $start, $limit);

        return $this->wrap($this->filter($lessons), $total);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course/Lesson', $res);
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
}