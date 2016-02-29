<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class JSONAPIController extends BaseController
{
    public function coursesAction(Request $request)
    {
        $keyword = $request->query->get('q');
        $courses = $this->getCourseService()->findCoursesByLikeTitle($keyword);
        return $this->createJsonResponse($courses);
    }

    public function usersAction(Request $request)
    {
        $keyword = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(array('nickname' => $keyword), array('createdTime', 'DESC'), 0, 100);
        return $this->createJsonResponse($users);
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }
}