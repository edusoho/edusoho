<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyCourseController extends BaseController
{
    private function renderViewForTeacher()
    {
        $currentUser = $this->getCurrentUser();
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->getUserTeachingCoursesCount($currentUser['id']),
            5
        );
        $teachingCourses = $this->getCourseService()->findUserTeachingCourses($currentUser['id'],$paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        return $this->render('TopxiaWebBundle:MyCourse:my-teaching-courses.html.twig', 
            array('teachingCourses'=>$teachingCourses,
                'roles'=>$currentUser['roles'],
                'paginator' => $paginator));
    }

    private function renderViewForStudent()
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
                'roles'=>$currentUser['roles'],
                'users'=>$users,
                'paginator' => $paginator));
    }

    public function indexAction (Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if(in_array('ROLE_TEACHER', $currentUser['roles'])){
            return $this->renderViewForTeacher();
        } else {
            return $this->renderViewForStudent();
        }
    }

    public function myCoursesAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if(in_array('ROLE_TEACHER', $currentUser['roles'])){
            return $this->renderViewForTeacher();
        } else {
            return $this->renderViewForStudent();
        }
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

        return $this->render('TopxiaWebBundle:MyCourse:my-learning-courses.html.twig', 
            array('learningCourses'=>$learningCourses,
                'roles'=>$currentUser['roles'],
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

        return $this->render('TopxiaWebBundle:MyCourse:my-learned-courses.html.twig', 
            array('learnedCourses'=>$learnedCourses,
                'roles'=>$currentUser['roles'],
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
        return $this->render('TopxiaWebBundle:MyCourse:my-teaching-courses.html.twig', 
            array('teachingCourses'=>$teachingCourses,
                'roles'=>$currentUser['roles'],
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
        return $this->render('TopxiaWebBundle:MyCourse:my-favorite-courses.html.twig', 
            array('favoriteCourses'=>$favoriteCourses,
                'roles'=>$currentUser['roles'],
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