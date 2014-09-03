<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassCoursesController extends BaseController
{

    public function showCourseAction(Request $request,$classId)
    {
    	$class = $this->getClassService()->getClass($classId);
    	 $conditions =array(
            'classId' => $classId
        );

        $total = $this->getCourseService()->searchCourseCount($conditions);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            0,
            $total
        );

        foreach ($courses as $key => $course) {
            foreach ($course['teacherIds'] as $key2 => $id) {

                $headTeacherProfile = $this->getUserService()->getUserProfile($id);
                $course['teachername'][$key2] = $headTeacherProfile['truename'];

            }
            $lessonCount = $this->getCourseService()
            	->searchLessonCount(array('courseId'=>$course['id']));
            $course['lessonCount'] = $lessonCount;
            $courses[$key] = $course;
        }


        return $this->render('TopxiaWebBundle:ClassCourses:show.html.twig',array(
        	'class' => $class,
        	'courses' => $courses,
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