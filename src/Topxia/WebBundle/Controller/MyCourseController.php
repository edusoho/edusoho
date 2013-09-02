<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyCourseController extends BaseController
{

    public function indexAction (Request $request)
    {
        $user = $this->getCurrentUser();
        if(in_array('ROLE_TEACHER', $user['roles'])) {
            return $this->forward('TopxiaWebBundle:MyCourse:teaching');
        }

        return $this->forward('TopxiaWebBundle:MyCourse:learning');
    }

    public function teachingAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserTeachingCoursesCount($user['id']),
            12
        );
        
        $courses = $this->getCourseService()->findUserTeachingCourses(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:MyCourse:teaching.html.twig', array(
            'courses'=>$courses,
            'paginator' => $paginator
        ));
    }

    public function learningAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserLeaningCoursesCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeaningCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();

        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourse:learning.html.twig', array(
            'courses'=>$courses,
            'users'=>$users,
            'paginator' => $paginator
        ));
    }

    public function learnedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserLeanedCoursesCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeanedCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourse:learned.html.twig', array(
            'courses'=>$courses,
            'users'=>$users,
            'paginator' => $paginator
        ));
    }

    public function favoritedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserFavoriteCourseCount($currentUser['id']),
            12
        );
        
        $courses = $this->getCourseService()->findUserFavoriteCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();
        foreach ($courses as $favoriteCourse) {
            $userIds = array_merge($userIds, $favoriteCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourse:favorited.html.twig', array(
            'courses'=>$courses,
            'users'=>$users,
            'paginator' => $paginator
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}