<?php
namespace Topxia\Api\Resource;

use Biz\Course\Service\Impl\CourseServiceImpl;
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
        return $res;
    }

    /**
     * @return CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}