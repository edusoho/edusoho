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

    public function index1Action(Request $request)
    {
     //   $course = $this->getCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:CourseManage:Report/modle.html.twig', array(
          //  'course' => $course
        ));
    }
     public function index2Action(Request $request, $id)
    {
       $course = $this->getCourseService()->getCourse($id);

        return $this->render('TopxiaWebBundle:CourseManage:Report/index2.html.twig', array(
            'course' => $course
        ));
    }



      public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}