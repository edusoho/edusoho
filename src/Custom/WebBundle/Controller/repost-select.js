<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class StudentReportController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
       
    	$course = $this->getCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:CourseManage:Report/index.html.twig', array(
            'course' => $course
        ));
    }


        public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}