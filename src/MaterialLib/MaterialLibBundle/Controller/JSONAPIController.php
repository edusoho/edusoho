<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class JSONAPIController extends BaseController
{
    public function coursesAction(Request $request)
    {
        $keyword = $request->query->get('q');
        if ($keyword) {
            $courses = $this->getCourseService()->findCoursesByLikeTitle($keyword);

            return $this->createJsonResponse($courses);
        }

        $courseId = $request->query->get('courseId');
        if ($courseId) {
            $course = $this->getCourseService()->getCourse($courseId);

            return $this->createJsonResponse($course);
        }

        return $this->createJsonResponse(null);
    }

    public function usersAction(Request $request)
    {
        $keyword = $request->query->get('q');
        if ($keyword) {
            $users = $this->getUserService()->searchUsers(array('nickname' => $keyword), array('createdTime', 'DESC'), 0, 100);

            return $this->createJsonResponse($users);
        }

        $userId = $request->query->get('userId');
        if ($userId) {
            $user = $this->getUserService()->getUser($userId);

            return $this->createJsonResponse($user);
        }

        return $this->createJsonResponse(null);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
