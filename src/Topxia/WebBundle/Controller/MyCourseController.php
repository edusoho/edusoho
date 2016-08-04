<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class MyCourseController extends BaseController
{
    public function indexAction(Request $request)
    {
        if ($this->getCurrentUser()->isTeacher()) {
            return $this->redirect($this->generateUrl('my_teaching_courses'));
        } else {
            return $this->redirect($this->generateUrl('my_courses_learning'));
        }
    }

    public function learningAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator   = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLeaningCourseCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeaningCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:MyCourse:learning.html.twig', array(
            'courses'   => $courses,
            'paginator' => $paginator
        ));
    }

    public function learnedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $paginator   = new Paginator(
            $this->get('request'),
            $this->getCourseService()->findUserLeanedCourseCount($currentUser['id']),
            12
        );

        $courses = $this->getCourseService()->findUserLeanedCourses(
            $currentUser['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array();
        foreach ($courses as $key => $course) {
            $userIds   = array_merge($userIds, $course['teacherIds']);
            $learnTime = $this->getCourseService()->searchLearnTime(array('courseId' => $course['id'], 'userId' => $currentUser['id']));

            $courses[$key]['learnTime'] = intval($learnTime / 60 / 60)."小时".($learnTime / 60 % 60)."分钟";
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaWebBundle:MyCourse:learned.html.twig', array(
            'courses'   => $courses,
            'users'     => $users,
            'paginator' => $paginator
        ));
    }

    public function favoritedAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        $conditions = array(
            'userId' => $currentUser['id']
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseService()->searchCourseFavoriteCount($conditions),
            12
        );

        $courseFavorites = $this->getCourseService()->searchCourseFavorites(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:MyCourse:favorited.html.twig', array(
            'courseFavorites' => $courseFavorites,
            'paginator'       => $paginator
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
