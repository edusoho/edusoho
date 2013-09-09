<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class DefaultController extends BaseController
{

    public function findWelcomedCoursesAction(Request $request)
    {
        $dateType = $request->query->get('dateType');
        $welcomedCourses = $this->_findWelcomedCourses($dateType);

        $html = $this->renderView('TopxiaAdminBundle:Default:block-welcomed-courses.html.twig', array(
                'welcomedCourses' => $welcomedCourses
            ));
        
        return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
    }

    public function indexAction(Request $request)
    {
        $welcomedCourses = $this->_findWelcomedCourses('today');
        return $this->render('TopxiaAdminBundle:Default:index.html.twig', array('welcomedCourses'=>$welcomedCourses));
    }

    public function latestUsersBlockAction(Request $request)
    {
        $users = $this->getUserService()->searchUsers(array(), array('createdTime', 'DESC'), 0, 5);
        return $this->render('TopxiaAdminBundle:Default:latest-users-block.html.twig', array(
            'users'=>$users,
        ));
    }

    public function unsolvedQuestionsBlockAction(Request $request)
    {
        $questions = $this->getThreadService()->searchThreads(
            array('type' => 'question', 'postNum' => 0),
            'createdNotStick',
            0,5
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($questions, 'courseId'));
        $askers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

        $teacherIds = array();
        foreach (ArrayToolkit::column($courses, 'teacherIds') as $teacherId) {
             $teacherIds = array_merge($teacherIds,$teacherId);
        }
        $teachers = $this->getUserService()->findUsersByIds($teacherIds);        

        return $this->render('TopxiaAdminBundle:Default:unsolved-questions-block.html.twig', array(
            'questions'=>$questions,
            'courses'=>$courses,
            'askers'=>$askers,
            'teachers'=>$teachers
        ));
    }

    public function latestPaidOrdersBlockAction(Request $request)
    {
        $orders = $this->getOrderService()->searchOrders(array('status'=>'paid'), 'latest', 0 , 5);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($orders, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($orders, 'userId'));
        
        return $this->render('TopxiaAdminBundle:Default:latest-paid-orders-block.html.twig', array(
            'orders'=>$orders,
            'users'=>$users,
            'courses'=>$courses
        ));
    }

    public function remindCourseTeachersAction(Request $request, $courseId, $questionId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $question = $this->getThreadService()->getThread($courseId, $questionId);
        $questionUrl = $this->generateUrl('course_thread_show', array('courseId'=>$course['id'], 'id'=> $question['id']), true);
        foreach ($course['teacherIds'] as $receiverId) {
            $result = $this->getNotificationService()->notify($receiverId, 'default',
                "这是来自后台管理者的通知: 你好, 您的课程: <<{$course['title']}>> 还有尚未解答的问题:  
                <a href='{$questionUrl}'> {$question['title']} </a> ,请及时提供答案!");
        }

        return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
    }

    private function _findWelcomedCourses($dateType)
    {
        $courseMembers = $this->getCourseService()->searchMember(array('date'=>$dateType, 'role'=>'student'), 0 , 1000);
        $welcomedCourses = array();

        foreach ($courseMembers as $courseMember) {
            $courseId = $courseMember['courseId'];
            if(empty($welcomedCourses[$courseId])){
                $welcomedCourses[$courseId] = array('newStudentsNumber'=>1);
            } else {
                $welcomedCourses[$courseId]['newStudentsNumber']++;
            }
        }

        foreach ($welcomedCourses as $courseId => &$welcomedCourse) {
            $allStudentsNumber = $this->getCourseService()->searchMemberCount(array('courseId'=>$courseId, 'role'=>'student'));
            $welcomedCourse['allStudentsNumber'] = $allStudentsNumber;
            $welcomedCourse['newMoneyAdded'] = 0;
            $welcomedCourse['course'] = $this->getCourseService()->getCourse($courseId);

            $orders = $this->getOrderService()->searchOrders(array('date'=>$dateType, 'courseId'=>$courseId), 'latest', 0, 1000);
            foreach ($orders as $id => $order) {
                $welcomedCourse['newMoneyAdded'] += $order['price'];
            }
        }
        return $welcomedCourses;
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

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}
