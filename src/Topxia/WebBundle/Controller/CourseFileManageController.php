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

    public function materialAction(Request $request, $id)
    {
        $paginator = new Paginator($request, 100);
        $course = $this->getCourseService()->tryManageCourse($id);
        $courseMaterials = array();

        return $this->render('TopxiaWebBundle:CourseFileManage:materials.html.twig', array(
            'course' => $course,
            'courseMaterials' => $courseMaterials,
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
        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-material.html.twig', array(
            'course' => $course
        ));
    }

    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        $ids = array();

        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->createJsonResponse(true);
    }

    public function renameCourseFilesAction(Request $request, $id, $type)
    {
        $ids = array();

    	$course = $this->getCourseService()->tryManageCourse($id);

        return $this->render('TopxiaWebBundle:CourseFileManage:modal-rename-course-files.html.twig', array(
            'course' => $course
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

}