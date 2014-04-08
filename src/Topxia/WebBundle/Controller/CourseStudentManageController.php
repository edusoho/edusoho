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
        $course = $this->getCourseService()->tryManageCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->getCourseStudentCount($course['id']),
            20
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
        $course = $this->getCourseService()->tryAdminCourse($id);

        $currentUser = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $user = $this->getUserService()->getUserByNickname($data['nickname']);
            if (empty($user)) {
                throw $this->createNotFoundException("用户{$data['nickname']}不存在");
            }

            $order = $this->getOrderService()->createOrder(array(
                'userId' => $user['id'],
                'title' => "购买课程《{$course['title']}》(管理员添加)",
                'targetType' => 'course',
                'targetId' => $course['id'],
                'amount' => $data['price'],
                'payment' => 'none',
                'snPrefix' => 'C',
            ));

            $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['amount'], 
                'paidTime' => time(),
            ));

            $info = array(
                'orderId' => $order['id'],
                'note'  => $data['remark'],
            );

            $this->getCourseService()->becomeStudent($order['targetId'], $order['userId'], $info);

            $member = $this->getCourseService()->getCourseMember($course['id'], $user['id']);

            $this->getNotificationService()->notify($member['userId'], 'student-create', array(
                'courseId' => $course['id'], 
                'courseTitle' => $course['title'],
            ));



            $this->getLogService()->info('course', 'add_student', "课程《{$course['title']}》(#{$course['id']})，添加学员{$user['nickname']}(#{$user['id']})，备注：{$data['remark']}");

            return $this->createStudentTrResponse($course, $member);
        }

        return $this->render('TopxiaWebBundle:CourseStudentManage:create-modal.html.twig',array(
            'course'=>$course
        ));
    }

    public function removeAction(Request $request, $courseId, $userId)
    {
        $course = $this->getCourseService()->tryAdminCourse($courseId);

        $this->getCourseService()->removeStudent($courseId, $userId);

        $this->getNotificationService()->notify($userId, 'student-remove', array(
            'courseId' => $course['id'], 
            'courseTitle' => $course['title'],
        ));

        return $this->createJsonResponse(true);
    }

    public function exportCsvAction (Request $request, $id)
    {   
        $course = $this->getCourseService()->tryAdminCourse($id);

        $courseMembers = $this->getCourseService()->findCourseStudents($course['id'],0,1000);

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);

        $progresses = array();
        foreach ($courseMembers as $student) {
            $progresses[] = $this->calculateUserLearnProgress($course, $student);
        }
        $str = "用户名,加入学习时间,学习进度,姓名,Email,公司,头衔,电话,微信号,QQ号"."\r\n";

        $students = array_map(function($user,$courseMember,$progress,$profile){
            $member['nickname']   = $user['nickname'];
            $member['joinedTime'] = date('Y-n-d H:i:s', $courseMember['createdTime']);
            $member['percent']  = $progress['percent'];
            $member['truename'] = $profile['truename'] ? $profile['truename'] : "-";
            $member['email'] = $user['email'] ? $user['email'] : "-";
            $member['company'] = $profile['company'] ? $profile['company'] : "-";
            $member['title'] = $user['title'] ? $user['title'] : "-";
            $member['mobile'] = $profile['mobile'] ? $profile['mobile'] : "-";
            $member['weixin'] = $profile['weixin'] ? $profile['weixin'] : "-";
            $member['qq'] = $profile['qq'] ? $profile['qq'] : "-";
            return implode(',',$member);
        }, $users,$courseMembers,$progresses,$profiles);
        $str .= implode("\r\n",$students);
        $str = chr(239) . chr(187) . chr(191) . $str;

        $filename = sprintf("course-%s-students-(%s).csv", $course['id'], date('Y-n-d'));

        $userId = $this->getCurrentUser()->id;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    public function remarkAction(Request $request, $courseId, $userId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $user = $this->getUserService()->getUser($userId);
        $member = $this->getCourseService()->getCourseMember($courseId, $userId);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $member = $this->getCourseService()->remarkStudent($course['id'], $user['id'], $data['remark']);
            return $this->createStudentTrResponse($course, $member);
        }

        return $this->render('TopxiaWebBundle:CourseStudentManage:remark-modal.html.twig',array(
            'member'=>$member,
            'user'=>$user,
            'course'=>$course
        ));
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

    public function showAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $profile = $this->getUserService()->getUserProfile($id);
        $profile['title'] = $user['title'];
        return $this->render('TopxiaWebBundle:CourseStudentManage:show-modal.html.twig', array(
            'user' => $user,
            'profile' => $profile,
        ));
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

    private function createStudentTrResponse($course, $student)
    {
        $user = $this->getUserService()->getUser($student['userId']);
        $isFollowing = $this->getUserService()->isFollowed($this->getCurrentUser()->id, $student['userId']);
        $progress = $this->calculateUserLearnProgress($course, $student);

        return $this->render('TopxiaWebBundle:CourseStudentManage:tr.html.twig', array(
            'course' => $course,
            'student' => $student,
            'user'=>$user,
            'progress' => $progress,
            'isFollowing' => $isFollowing,
        ));
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
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}