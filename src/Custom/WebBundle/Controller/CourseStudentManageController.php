<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/23
 * Time: 11:14
 */

namespace Custom\WebBundle\Controller;

use Custom\Service\School\Impl\SchoolServiceImpl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\CourseStudentManageController as BaseCourseStudentController;

class CourseStudentManageController extends BaseCourseStudentController
{
    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $fields = $request->query->all();

        $condition = array('courseId'=>$course['id'],'role'=>'student');

        $condition = array_merge($condition, $fields);
        $paginator = new Paginator(
            $request,
            $this->getCourseService()->searchMemberCount($condition),
            20
        );

        $students = $this->getCourseService()->searchMembers(
            $condition,
            array('createdTime','DESC'),
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

        $courseSetting = $this->getSettingService()->get('course', array());
        $isTeacherAuthManageStudent = !empty($courseSetting['teacher_manage_student']) ? 1: 0;
        $default = $this->getSettingService()->get('default', array());
        return $this->render('TopxiaWebBundle:CourseStudentManage:index.html.twig', array(
            'course' => $course,
            'students' => $students,
            'users'=>$users,
            'progresses' => $progresses,
            'followingIds' => $followingIds,
            'isTeacherAuthManageStudent' => $isTeacherAuthManageStudent,
            'paginator' => $paginator,
            'canManage' => $this->getCourseService()->canManageCourse($course['id']),
            'default'=>$default
        ));

    }

    public function exportCsvAction (Request $request, $id)
    {
        $gender=array('female'=>'女','male'=>'男','secret'=>'秘密');
        $courseSetting = $this->getSettingService()->get('course', array());
        $organization = $this->getOrganizationService()->findAllOrganizations();
        if (isset($courseSetting['teacher_export_student']) && $courseSetting['teacher_export_student']=="1") {
            $course = $this->getCourseService()->tryManageCourse($id);
        } else {
            $course = $this->getCourseService()->tryAdminCourse($id);
        }

        $userinfoFields=array();
        if(isset($courseSetting['userinfoFields'])){
            $userinfoFields=array_diff($courseSetting['userinfoFields'], array('truename','job','mobile','qq','company','gender','idcard','weixin'));
        }

        $courseMembers = $this->getCourseService()->searchMembers( array('courseId' => $course['id'],'role' => 'student'),array('createdTime', 'DESC'), 0, 1000);

        $userFields=$this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        $fields['weibo']="微博";
        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']]=$userField['title'];
        }

        $userinfoFields=array_flip($userinfoFields);

        $fields=array_intersect_key($fields, $userinfoFields);

        if(!$courseSetting['buy_fill_userinfo']){
            $fields=array();
        }
        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $progresses = array();
        foreach ($courseMembers as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($course, $student);
        }

        $str = "用户名,学号,院系/专业,Email,加入学习时间,学习进度,姓名,性别,QQ号,微信号,手机号,头衔";

        foreach ($fields as $key => $value) {
            $str.=",".$value;
        }
        $str.="\r\n";

        $students = array();

        foreach ($courseMembers as $courseMember) {
            $member = "";
            $member .= $users[$courseMember['userId']]['nickname'].",";
            $member .= $users[$courseMember['userId']]['staffNo'] ? $users[$courseMember['userId']]['staffNo']."," : "-,";
            if(!empty($organization[$users[$courseMember['userId']]['organizationId']])){
                $member .= $organization[$users[$courseMember['userId']]['organizationId']]['name'].",";
            }else{
                $member .= "-,";
            }

            $member .= $users[$courseMember['userId']]['email'].",";
            $member .= date('Y-n-d H:i:s', $courseMember['createdTime']).",";
            $member .= $progresses[$courseMember['userId']]['percent'].",";
            $member .= $profiles[$courseMember['userId']]['truename'] ? $profiles[$courseMember['userId']]['truename']."," : "-".",";
            $member .= $gender[$profiles[$courseMember['userId']]['gender']].",";
            $member .= $profiles[$courseMember['userId']]['qq'] ? $profiles[$courseMember['userId']]['qq']."," : "-".",";
            $member .= $profiles[$courseMember['userId']]['weixin'] ? $profiles[$courseMember['userId']]['weixin']."," : "-".",";
            $member .= $profiles[$courseMember['userId']]['mobile'] ? $profiles[$courseMember['userId']]['mobile']."," : "-".",";
            $member .= $users[$courseMember['userId']]['title'] ? $users[$courseMember['userId']]['title']."," : "-".",";
            foreach ($fields as $key => $value) {
                $member.=$profiles[$courseMember['userId']][$key] ? $profiles[$courseMember['userId']][$key]."," : "-".",";
            }
            $students[] = $member;
        };

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

    protected function getOrganizationService()
    {
        return $this->getServiceKernel()->createService('Custom:Organization.OrganizationService');
    }
}