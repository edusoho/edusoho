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
    	$paginator = new Paginator($request, 1);
        $course = $this->getCourseService()->tryManageCourse($id);
        $user = $this->getCurrentUser();
        $courseWares = array(
            array(
                'id'=>1,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ),
            array(
                'id'=>2,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ));

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'course' => $course,
            'courseWares' => $courseWares,
            'paginator' => $paginator
        ));
    }

    public function materialAction(Request $request, $id)
    {
        $paginator = new Paginator($request, 1);
        $course = $this->getCourseService()->tryManageCourse($id);
        $user = $this->getCurrentUser();
        $courseMaterials = array(
            array(
                'id'=>1,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ),
            array(
                'id'=>2,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
        ));

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

    public function processingUploadingCourseWareAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        exit();
        
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
        $ids = $request->request->get('ids', array());

        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->createJsonResponse(true);
    }

    public function renameCourseFilesAction(Request $request, $id, $type)
    {
        $ids = $request->request->get('ids', array());
        $user = $this->getCurrentUser();
    	$course = $this->getCourseService()->tryManageCourse($id);
        
        $courseMaterials = array(
            array(
                'id'=>1,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
                ),
            array(
                'id'=>2,
                'fileName'=>'从入门到精通',
                'fileType'=>'audio',
                'fileSize'=>1000,
                'updateTime'=>1383190130,
                'updateUser'=>$user,
                'createdTime'=>1383190130,
                'createdUser'=>$user
        ));

        $html = $this->renderView('TopxiaWebBundle:CourseFileManage:modal-rename-course-files.html.twig', array(
            'course' => $course,
            'courseFiles' => $courseMaterials));
        return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
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