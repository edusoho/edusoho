<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseFileManageController extends BaseController
{

    public function indexAction(Request $request, $id)
    {
    	$paginator = new Paginator($request, 100);
        $course = $this->getCourseService()->tryManageCourse($id);
        $courseWares = array();

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'course' => $course,
            'courseWares' => $courseWares,
            'paginator' => $paginator
        ));
    }

    public function uploadCourseWareAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-ware.html.twig', array(
            'course' => $course
        ));
    }

    public function uploadCourseMaterialAction(Request $request, $id)
    {
    	$paginator = new Paginator($request, 100);
        $course = $this->getCourseService()->tryManageCourse($id);
        $courseWares = array();

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'course' => $course,
            'courseWares' => $courseWares,
            'paginator' => $paginator
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }


}