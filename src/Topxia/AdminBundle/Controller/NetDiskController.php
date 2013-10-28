<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceException;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class NetDiskController extends BaseController
{
    public function indexAction(Request $request)
    {
    	$paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseCount(array()),
            20
        );

        $courses = $this->getCourseService()->searchCourses(
            array(),
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

	  	$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        return $this->render('TopxiaAdminBundle:NetDisk:index.html.twig', array(
        	'courses'=>$courses,
        	'paginator' => $paginator,
        	'users' => $users
        ));

    }

    public function findCourseFilesAction(Request $request, $courseId)
    {
        // $this->getCourseFileService()->getFileTreeByCourseId($courseId);

        $course = $this->getCourseService()->getCourse($courseId);
        $createUser = $this->getUserService()->getUser($course['userId']);
        $fileTrees = array('resource'=>array('1','2','3'));
        $mediaFiles = array(
            '1.mp4',
            '1.mp4',
            '1.mp4',
            '1.mp4',
            '1.mp4',
            '1.mp4',
            '1.mp4',
            '2.mp4');

        return $this->render('TopxiaAdminBundle:NetDisk:course-file-tree.html.twig', array(
            'course' => $course,
            'mediaFiles' => $mediaFiles,
            'createUser' => $createUser
        ));
    }

    public function uploadFilesAction(Request $request)
    {
        var_dump($request);
        exit();
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCourseFileService()
    {
        return $this->getServiceKernel()->createService('Course.CourseFileService');
    }


}