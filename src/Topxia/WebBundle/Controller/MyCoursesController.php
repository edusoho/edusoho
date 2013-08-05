<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyCoursesController extends BaseController
{

    public function indexAction (Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserLeaningCoursesCount($currentUser['id']),
            5
        );

        $learningCourses = $this->getCourseService()->findUserLeaningCourses($currentUser['id'], $paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $userIds = array();
        foreach ($learningCourses as $learningCourse) {
            $userIds = array_merge($userIds, $learningCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:My:index.html.twig', 
            array('learningCourses'=>$learningCourses,
                'users'=>$users,
                'paginator' => $paginator));
    }

    public function myCoursesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserLeaningCoursesCount($currentUser['id']),
            5
        );

        $learningCourses = $this->getCourseService()->findUserLeaningCourses($currentUser['id'], $paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $userIds = array();
        foreach ($learningCourses as $learningCourse) {
            $userIds = array_merge($userIds, $learningCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourses:my-courses.html.twig', 
            array('learningCourses'=>$learningCourses,
                'users'=>$users,
                'paginator' => $paginator));
    }


    public function myLearningCoursesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserLeaningCoursesCount($currentUser['id']),
            5
        );

        $learningCourses = $this->getCourseService()->findUserLeaningCourses($currentUser['id'], $paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $userIds = array();
        foreach ($learningCourses as $learningCourse) {
            $userIds = array_merge($userIds, $learningCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourses:my-learning-courses.html.twig', 
            array('learningCourses'=>$learningCourses,
                'users'=>$users,
                'paginator' => $paginator));
    }

    public function myLearnedCoursesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserLeanedCoursesCount($currentUser['id']),
            5
        );

        $learnedCourses = $this->getCourseService()->findUserLeanedCourses($currentUser['id'],$paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $userIds = array();
        foreach ($learnedCourses as $learnedCourse) {
            $userIds = array_merge($userIds, $learnedCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourses:my-learned-courses.html.twig', 
            array('learnedCourses'=>$learnedCourses,
                'users'=>$users,
                'paginator' => $paginator));
    }

    public function myTeachingCoursesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserTeachingCoursesCount($currentUser['id']),
            5
        );
        
        $teachingCourses = $this->getCourseService()->findUserTeachingCourses($currentUser['id'],$paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        return $this->render('TopxiaWebBundle:MyCourses:my-teaching-courses.html.twig', 
            array('teachingCourses'=>$teachingCourses,
                'paginator' => $paginator));
    }

    public function myFavoriteCoursesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserFavoriteCourseCount($currentUser['id']),
            5
        );
        
        $favoriteCourses = $this->getCourseService()->findUserFavoriteCourses($currentUser['id'],$paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        $userIds = array();
        foreach ($favoriteCourses as $favoriteCourse) {
            $userIds = array_merge($userIds, $favoriteCourse['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);
        return $this->render('TopxiaWebBundle:MyCourses:my-favorite-courses.html.twig', 
            array('favoriteCourses'=>$favoriteCourses,
                'users'=>$users,
                'paginator' => $paginator));
    }
    

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
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