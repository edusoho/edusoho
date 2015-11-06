<?php
namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{
    public function indexAction()
    {
        return $this->render('TopxiaWebBundle:Default:index.html.twig');
    }

    public function recommedCoursesAction(Request $request)
    {
        $orderBy = $request->query->get('orderBy');
        $conditions = array('status' => 'published', 'recommended' => 1 ,'parentId' => 0);

        $courses = $this->getCourseService()->searchCourses($conditions, $orderBy, 0, 6);
        if(empty($courses)){
            unset($conditions['recommended']);
            $courses = $this->getCourseService()->searchCourses($conditions, $orderBy, 0, 3);
        }
        $userIds = array();

        foreach($courses as $course){
            $userIds = array_merge($userIds, $course['teacherIds']);
        }

        $teachers = $this->getUserService()->findUsersByIds($userIds);

        foreach ($courses as &$course){
            $course['teachers'] = array();
            foreach($course['teacherIds'] as $teacherId){
                unset($teachers[$teacherId]['salt']);
                unset($teachers[$teacherId]['password']);
                array_push($course['teachers'], $teachers[$teacherId]);
            }
        }


        return $this->render('TopxiaWebBundle:Default:course-item.html.twig', array(
            'courses' => $courses
        ));
    }

}