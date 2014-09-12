<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassCoursesController extends ClassBaseController
{

    public function listAction(Request $request,$classId)
    {
    	$class = $this->tryViewClass($classId);
        $conditions =array(
            'classId' => $classId,
            'status' => 'published',
            'gradeId' => $class['gradeId'],
            'term' => $class['term']
        );

        $total = $this->getCourseService()->searchCourseCount($conditions);

        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, $total);

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:ClassCourses:list.html.twig',array(
        	'class' => $class,
        	'courses' => $courses,
            'users' => $users,
    	));
    }
    
    protected function getClassService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }  
}