<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:Default:index.html.twig');
    }

    public function newUsersAction(Request $request)
    {
        $newUsers = $this->getUserService()->searchUsers(array(), 0 , 10);
        $favoriteCourses = array();
        foreach ($newUsers as $newUser) {
            $courses = $this->getCourseService()->findUserFavoriteCourses($newUser['id'], 0, 10);
            $favoriteCourses[$newUser['id']] = $courses;
        }

        return $this->render('TopxiaAdminBundle:Default:block-new-users.html.twig',
            array(
                'newUsers'=>$newUsers,
                'favoriteCourses'=>$favoriteCourses
            ));
    }

    public function unsolvedQuestionsAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(
            array('type'=>'question', 'postNum'=>0),
            'createdNotStick',
            0,10
        );
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $askers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $teacherIds = array();
        foreach (ArrayToolkit::column($courses, 'teacherIds') as $teacherId) {
             $teacherIds = array_merge($teacherIds,$teacherId);
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);        

        return $this->render('TopxiaAdminBundle:Default:block-unsolved-questions.html.twig', array(
            'questions'=>$questions,
            'courses'=>$courses,
            'askers'=>$askers,
            'teachers'=>$teachers
            ));
    }

    public function paidRecordsAction(Request $request)
    {
        $paidRecords = $this->getOrderService()->searchOrders(array(), 'latest', 0 , 10);
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($paidRecords, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($paidRecords, 'userId'));
        return $this->render('TopxiaAdminBundle:Default:block-paid-records.html.twig', array(
            'paidRecords'=>$paidRecords,
            'users'=>$users,
            'courses'=>$courses
        ));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }

}
