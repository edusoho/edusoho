<?php
namespace Topxia\WebBundle\Controller\Part;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

use Topxia\WebBundle\Controller\BaseController;

class CourseController extends BaseController
{
    public function guestHeaderAction($course)
    {

        if (($course['discountId'] > 0)&&($this->isPluginInstalled("Discount"))){
            $course['discountObj'] = $this->getDiscountService()->getDiscount($course['discountId']);
        }

        $hasFavorited = $this->getCourseService()->hasFavoritedCourse($course['id']);

        $classrooms = $this->getClassroomService()->searchClassrooms(array('recommended' => 1), array('recommendedSeq', 'ASC'), 0, 11);

        $user = $this->getCurrentUser();
        $userVipStatus = $courseVip = null;
        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $courseVip = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;
            if ($courseVip) {
                $userVipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseVip['id']);
            }
        }

        return $this->render('TopxiaWebBundle:Course:Part/normal-header-for-guest.html.twig', array(
            'course' => $course,
            'hasFavorited' => $hasFavorited,
            'classrooms' => $classrooms,
            'courseVip' => $courseVip,
            'userVipStatus' => $userVipStatus,
        ));

    }
    public function teachersAction($course)
    {
        $course = $this->getCourse($course);
        $teachers = $this->getUserService()->findUsersByIds($course['teacherIds']);

        return $this->render('TopxiaWebBundle:Course:Part/normal-sidebar-teachers.html.twig', array(
            'course' => $course,
            'teachers' => $teachers,
        ));
    }

    public function studentsAction($course)
    {
        $course = $this->getCourse($course);
        $members = $this->getCourseService()->findCourseStudents($course['id'], 0, 15);
        $students = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        return $this->render('TopxiaWebBundle:Course:Part/normal-sidebar-students.html.twig', array(
            'course' => $course,
            'students' => $students,
        ));
    }

    protected function getCourse($course)
    {
        if (is_array($course)) {
            return $course;
        }

        $courseId = (int) $course;
        return $this->getCourseService()->getCourse($courseId);
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}

