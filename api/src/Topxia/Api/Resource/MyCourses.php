<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class MyCourses extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $stringIds        = $request->query->get('ids', '');
        $ids              = array_unique(explode(',', $stringIds));
        $user             = $this->getCurrentUser();
        $learnCourseCount = $this->getCourseService()->findUserLearnCourseCountNotInClassroom($user['id']);
        $learnCourses     = $this->getCourseService()->findUserLearnCoursesNotInClassroom($user['id'], 0, $learnCourseCount);

        $learnCourses = ArrayToolkit::index($learnCourses, 'id');

        foreach ($ids as $id) {
            unset($learnCourses[$id]);
        }

        return $this->wrap($this->filter($learnCourses), $learnCourseCount);
    }

    public function filter(&$res)
    {
        return $this->multicallFilter('MyCourse', $res);
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
