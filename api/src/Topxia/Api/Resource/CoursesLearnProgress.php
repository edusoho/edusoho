<?php
namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CoursesLearnProgress extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $courseIds = $request->query->get('courseIds', 0);
        $currentUser = $this->getCurrentUser();
        $courseIds = explode(',', $courseIds);
        $progressData =  $this->getCourseService()->calculateLearnProgressByUserIdAndCourseIds($currentUser['id'], $courseIds);
        return $this->wrap($progressData, count($progressData));
    }

    public function filter($res)
    {
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}