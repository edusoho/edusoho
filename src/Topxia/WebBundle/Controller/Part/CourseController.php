<?php
namespace Topxia\WebBundle\Controller\Part;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

use Topxia\WebBundle\Controller\BaseController;

class CourseController extends BaseController
{
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

    public function favoriteAction($course)
    {
        $hasFavorited = $this->getCourseService()->hasFavoritedCourse($course['id']);
        
    }

    protected function getCourse($course)
    {
        if (is_array($course)) {
            return $course;
        }

        $courseId = (int) $course;
        return $this->getCourseService()->getCourse($courseId);
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

