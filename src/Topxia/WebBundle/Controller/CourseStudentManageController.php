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
                'courseId' => $course['id'],
                'userId' => $user['id'],
                'price' => $data['price'],
                'payment' => 'none',
            ));

            $this->getOrderService()->payOrder(array(
                'sn' => $order['sn'],
                'status' => 'success', 
                'amount' => $order['price'], 
                'paidTime' => time(),
                'memberRemark' => $data['remark'],
            ));

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


    public function exportAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $paginator = new Paginator(
            $request,
            $this->getCourseService()->getCourseStudentCount($course['id']),
            1000
        );

        $students = $this->getCourseService()->findCourseStudents(
            $course['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);

        $studentsInfo="";

        foreach ($users as $user) {

            $profile = $profiles[$user['id']];

            $studentsInfo=$studentsInfo.$user['nickname'].",".$user['email'];

            if(! empty($profile['truename'])){
                 $studentsInfo =$studentsInfo.",".$profile['truename'];
            }
            if(! empty($profile['mobile'])){
                 $studentsInfo =$studentsInfo.",".$profile['mobile'];
            }
            if(! empty($profile['job'])){
                 $studentsInfo =$studentsInfo.",".$profile['job'];
            }
            if(! empty($profile['company'])){
                 $studentsInfo =$studentsInfo.",".$profile['company'];
            }
            $studentsInfo =$studentsInfo."\n";
               

        }

        return $this->render('TopxiaWebBundle:CourseStudentManage:students-modal.html.twig', array(
            'course' => $course,
            'studentsInfo' => $studentsInfo,
           
            'canManage' => $this->getCourseService()->canManageCourse($course['id']),
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
        return $this->getServiceKernel()->createService('Course.OrderService');
    }
}