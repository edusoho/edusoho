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
        var_dump($courseId);
        return $this->render('TopxiaAdminBundle:NetDisk:index.html.twig', array(
        ));

    }


    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }


}