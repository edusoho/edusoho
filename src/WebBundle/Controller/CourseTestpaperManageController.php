<?php
namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseTestpaperManageController extends BaseController
{
    public function checkAction(Request $request, $courseId, $resultId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        return $this->forward('WebBundle:TestpaperManage:check', array(
            'request'  => $request,
            'resultId' => $resultId,
            'source'   => 'course',
            'targetId' => $course['id']
        ));
    }

    public function checkListAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $user      = $this->getUser();
        $isTeacher = $this->getCourseService()->hasTeacherRole($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('WebBundle:CourseTestpaperManage:check-list.html.twig', array(
            'course'    => $course,
            'isTeacher' => $isTeacher
        ));
    }

    public function resultListAction(Request $request, $id, $testpaperId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $user   = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperId);
        }

        $isTeacher = $this->getCourseService()->hasTeacherRole($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('WebBundle:CourseTestpaperManage:result-list.html.twig', array(
            'course'    => $course,
            'testpaper' => $testpaper,
            'isTeacher' => $isTeacher
        ));
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
