<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassCoursesController extends BaseController
{

    /**
     * @todo  缺少权限判断
     */
    public function listAction(Request $request,$classId)
    {
    	$class = $this->getClassService()->getClass($classId);
        $conditions =array(
            'classId' => $classId,
            'status' => 'published'
        );

        $total = $this->getCourseService()->searchCourseCount($conditions);

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            0,
            $total
        );

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $users = $this->getUserService()->findUsersByIds($userIds);

        // foreach ($courses as $key => $course) {
        //     foreach ($course['teacherIds'] as $key2 => $id) {

        //         $teacher = $this->getUserService()->getUser($id);
        //         $course['teacher'][$key2] = $teacher;

        //     }
        //     $lessonCount = $this->getCourseService()
        //     	->searchLessonCount(array('courseId'=>$course['id']));
        //     $course['lessonCount'] = $lessonCount;
        //     $courses[$key] = $course;
        // }


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