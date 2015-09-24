<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/24
 * Time: 17:13
 */

namespace Custom\WebBundle\Controller\Classroom;

use Classroom\ClassroomBundle\Controller\ClassroomManageController as BaseClassroomManageController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassroomManageController extends BaseClassroomManageController
{

    public function studentsAction(Request $request, $id, $role = 'student')
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = $request->query->all();

        $conditions = array(
            'classroomId' => $id,
            'role' => 'student'
        );

        $conditions = array_merge($conditions, $fields);

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($conditions),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        $progresses = array();
        foreach ($students as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($classroom, $student);
        }

        return $this->render("ClassroomBundle:ClassroomManage:student.html.twig", array(
            'classroom' => $classroom,
            'students' => $students,
            'users' => $users,
            'progresses' => $progresses,
            'paginator' => $paginator,
            'role' => $role,
        ));
    }

    public function aduitorAction(Request $request, $id, $role = 'auditor')
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $classroom = $this->getClassroomService()->getClassroom($id);

        $fields = $request->query->all();

        $conditions = array(
            'classroomId' => $id,
            'role' => 'auditor'
        );

        $conditions = array_merge($conditions, $fields);

        $paginator = new Paginator(
            $request,
            $this->getClassroomService()->searchMemberCount($conditions),
            20
        );

        $students = $this->getClassroomService()->searchMembers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentUserIds = ArrayToolkit::column($students, 'userId');
        $users = $this->getUserService()->findUsersByIds($studentUserIds);

        return $this->render("ClassroomBundle:ClassroomManage:auditor.html.twig", array(
            'classroom' => $classroom,
            'students' => $students,
            'users' => $users,
            'paginator' => $paginator,
            'role' => $role,
        ));
    }

    public function exportCsvAction(Request $request, $id, $role)
    {
        $this->getClassroomService()->tryManageClassroom($id);
        $gender = array('female' => '女','male' => '男','secret' => '秘密');
        $schoolOrganization = $this->getSchoolService()->findAllSchoolOrganization();
        $classroom = $this->getClassroomService()->getClassroom($id);

        $userinfoFields = array('truename','job','mobile','qq','company','gender','idcard','weixin');

        if ($role == 'student') {
            $classroomMembers = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['id'], 'role' => 'student'), array('createdTime', 'DESC'), 0, 1000);
        } else {
            $classroomMembers = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['id'], 'role' => 'auditor'), array('createdTime', 'DESC'), 0, 1000);
        }

        $userFields = $this->getUserFieldService()->getAllFieldsOrderBySeqAndEnabled();

        $fields['weibo'] = "微博";
        foreach ($userFields as $userField) {
            $fields[$userField['fieldName']] = $userField['title'];
        }

        $userinfoFields = array_flip($userinfoFields);

        $fields = array_intersect_key($fields, $userinfoFields);

        $studentUserIds = ArrayToolkit::column($classroomMembers, 'userId');

        $users = $this->getUserService()->findUsersByIds($studentUserIds);
        $users = ArrayToolkit::index($users, 'id');

        $profiles = $this->getUserService()->findUserProfilesByIds($studentUserIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $progresses = array();
        foreach ($classroomMembers as $student) {
            $progresses[$student['userId']] = $this->calculateUserLearnProgress($classroom, $student);
        }

        $str = "用户名,学号,院系/专业,Email,加入学习时间,学习进度,姓名,性别,QQ号,微信号,手机号,头衔";

        foreach ($fields as $key => $value) {
            $str .= ",".$value;
        }
        $str .= "\r\n";

        $students = array();

        foreach ($classroomMembers as $classroomMember) {
            $member = "";
            $member .= $users[$classroomMember['userId']]['nickname'].",";
            $member .= $users[$classroomMember['userId']]['staffNo'] ? $users[$classroomMember['userId']]['staffNo']."," : "-,";
            if(!empty($schoolOrganization[$users[$classroomMember['userId']]['schoolOrganizationId']])){
                $member .= $schoolOrganization[$users[$classroomMember['userId']]['schoolOrganizationId']]['name'].",";
            }else{
                $member .= "-,";
            }
            $member .= $users[$classroomMember['userId']]['email'].",";
            $member .= date('Y-n-d H:i:s', $classroomMember['createdTime']).",";
            $member .= $progresses[$classroomMember['userId']]['percent'].",";
            $member .= $profiles[$classroomMember['userId']]['truename'] ? $profiles[$classroomMember['userId']]['truename']."," : "-".",";
            $member .= $gender[$profiles[$classroomMember['userId']]['gender']].",";
            $member .= $profiles[$classroomMember['userId']]['qq'] ? $profiles[$classroomMember['userId']]['qq']."," : "-".",";
            $member .= $profiles[$classroomMember['userId']]['weixin'] ? $profiles[$classroomMember['userId']]['weixin']."," : "-".",";
            $member .= $profiles[$classroomMember['userId']]['mobile'] ? $profiles[$classroomMember['userId']]['mobile']."," : "-".",";
            $member .= $users[$classroomMember['userId']]['title'] ? $users[$classroomMember['userId']]['title']."," : "-".",";
            foreach ($fields as $key => $value) {
                $member .= $profiles[$classroomMember['userId']][$key] ? $profiles[$classroomMember['userId']][$key]."," : "-".",";
            }
            $students[] = $member;
        }

        $str .= implode("\r\n", $students);
        $str = chr(239).chr(187).chr(191).$str;

        $filename = sprintf("classroom-%s-students-(%s).csv", $classroom['id'], date('Y-n-d'));

        // $userId = $this->getCurrentUser()->id;

        $response = new Response();
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Content-length', strlen($str));
        $response->setContent($str);

        return $response;
    }

    private function calculateUserLearnProgress($classroom, $member)
    {
        $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
        $courseIds = ArrayToolkit::column($courses, 'id');
        $findLearnedCourses = array();
        foreach ($courseIds as $key => $value) {
            $learnedCourses = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($value, $member['userId']);
            if (!empty($learnedCourses)) {
                $findLearnedCourses[] = $learnedCourses;
            }
        }

        $learnedCoursesCount = count($findLearnedCourses);
        $coursesCount = count($courses);

        if ($coursesCount == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($learnedCoursesCount / $coursesCount * 100).'%';

        return array(
            'percent' => $percent,
            'number' => $learnedCoursesCount,
            'total' => $coursesCount,
        );
    }

    protected function getSchoolService()
    {
        return $this->getServiceKernel()->createService('Custom:School.SchoolService');
    }

}