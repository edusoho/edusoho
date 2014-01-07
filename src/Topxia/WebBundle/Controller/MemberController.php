<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MemberController extends BaseController
{
    public function indexAction()
    {	
    	$conditions = array();
    	$userlevels = $this->getUserService()->searchUserlevels($conditions,0,100);
    	$courses = $this->getCourseService()->findCoursesByHaveUserLevelIds(0, 100);
        return $this->render('TopxiaWebBundle:Member:index.html.twig',array(
        	'userlevels' => $userlevels,
            'courses' => $courses
        ));
    }

    public function courseAction(Request $request,$id)
    {   
        $conditions = array();
        $courses = $this->getCourseService()->findCoursesByUserLevelId($id);
        $userlevels = $this->getUserService()->searchUserlevels($conditions,0,100);
        return $this->render('TopxiaWebBundle:Member:course.html.twig',array(
            'courses' => $courses,
            'userlevels' =>$userlevels,
            'id' =>$id,
        ));
    }

    protected function getUserService()
    {
    	return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
    	return $this->getServiceKernel()->createService('Course.CourseService');
    }
}