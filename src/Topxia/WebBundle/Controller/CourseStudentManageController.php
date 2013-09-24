<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseStudentManageController extends BaseController
{

    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryTakeCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->getCourseStudentCount($course['id']),
            6
        );

        $students = $this->getCourseService()->findCourseStudents(
            $course['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $followingIds = $this->getUserService()->filterFollowingIds($this->getCurrentUser()->id, $studentUserIds);

        $progresses = array();
        foreach ($students as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($course, $student);
        }
        
        return $this->render('TopxiaWebBundle:CourseStudentManage:index.html.twig', array(
            'course' => $course,
            'students' => $students,
            'users'=>$users,
            'progresses' => $progresses,
            'followingIds' => $followingIds,
            'paginator' => $paginator,
            'canManage' => $this->getCourseService()->canManageCourse($course['id']),
        ));

    }

    public function createAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $currentUser = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {
            $courseUrl = $this->generateUrl('course_show', array('id'=>$course['id']), true);
            $data = $request->request->all();
            $user = $this->getUserService()->getUserByNickname($data['nickname']);

            $order = $this->getOrderService()->createOrder(array(
                'courseId' => $course['id'],
                'userId' => $user['id'],
                'price' => 0,
                'payment' => 'none',
            ));

            $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['price'], 
                'paidTime' => time()
            ));

            $member = $this->getCourseService()->joinCourse($user['id'], $course['id'], $data['remark']);
            // $this->getNotificationService()->notify($user['id'], 'default', "管理员{$currentUser['nickname']}已将你设置为课程<a href='{$courseUrl}' target='_blank'>《{$course['title']}》</a> 的学员了, 开始学习吧!");
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaWebBundle:CourseStudentManage:create-modal.html.twig',array(
            'course'=>$course));
    }

    public function removeAction(Request $request, $courseId, $userId)
    {
        $this->getCourseService()->exitCourse($userId, $courseId);
        return $this->createJsonResponse(true);
    }


    public function remarkAction(Request $request, $courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getCourseService()->getCourseMember($courseId, $userId);

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $this->getCourseService()->updateCourseMember($member['id'], array('remarks'=>$formData['remarks']));
            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaWebBundle:CourseManage:edit-remark-modal.html.twig',array(
            'member'=>$member,
            'user'=>$user,
            'course'=>$course));

    }

    public function checkNicknameAction(Request $request, $id)
    {
        $nickname = $request->query->get('value');
        $result = $this->getUserService()->isNicknameAvaliable($nickname);
        if ($result) {
            $response = array('success' => false, 'message' => '该用户不存在');
        } else {
            $user = $this->getUserService()->getUserByNickname($nickname);
            $isCourseStudent = $this->getCourseService()->isCourseStudent($id, $user['id']);
            if($isCourseStudent){
                $response = array('success' => false, 'message' => '该用户已是本课程的学员了');
            } else {
                $response = array('success' => true, 'message' => '');
            }
            
            $isCourseTeacher = $this->getCourseService()->isCourseTeacher($id, $user['id']);
            if($isCourseTeacher){
                $response = array('success' => false, 'message' => '该用户是本课程的教师，不能添加');
            }
        }
        return $this->createJsonResponse($response);
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum']
        );
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    private function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.OrderService');
    }
}