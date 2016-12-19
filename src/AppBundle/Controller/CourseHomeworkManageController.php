<?php
namespace AppBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseHomeworkManageController extends BaseController
{
    public function checkAction(Request $request, $id, $resultId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $course = $this->getCourseService()->tryManageCourse($course['id']);

        return $this->forward('AppBundle:HomeworkManage:check', array(
            'request'  => $request,
            'resultId' => $resultId,
            'source'   => 'course',
            'targetId' => $course['id']
        ));
    }

    public function checkListAction(Request $request, $id)
    {
        $course    = $this->getCourseService()->getCourse($id);
        $course    = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user      = $this->getUser();
        $isTeacher = $this->getCourseService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($course['id'], 'homework');

        return $this->render('course-manage/homework-check/check-list.html.twig', array(
            'courseSet'   => $courseSet,
            'course'      => $course,
            'isTeacher'   => $isTeacher,
            'homeworkIds' => ArrayToolkit::column($activities, 'mediaId')
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

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
