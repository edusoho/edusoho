<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class OpenCourseStudentManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        return $this->render('TopxiaWebBundle:OpenCourseStudentManage:index.html.twig', array('course' => $course));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
