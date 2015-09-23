<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/23
 * Time: 11:14
 */

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
}