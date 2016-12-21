<?php
namespace AppBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class CourseTestpaperManageController extends BaseController
{
    public function checkAction(Request $request, $id, $resultId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $course = $this->getCourseService()->tryManageCourse($course['id']);

        return $this->forward('AppBundle:Testpaper/Manage:check', array(
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

        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($course['id'], 'testpaper');

        $testpaperActivityIds = ArrayToolkit::column($activities, 'mediaId');

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByIds($testpaperActivityIds);

        return $this->render('course-manage/testpaper-check/check-list.html.twig', array(
            'courseSet'    => $courseSet,
            'course'       => $course,
            'isTeacher'    => $isTeacher,
            'testpaperIds' => ArrayToolkit::column($testpaperActivities, 'mediaId')
        ));
    }

    public function resultListAction(Request $request, $id, $testpaperId)
    {
        $course    = $this->getCourseService()->getCourse($id);
        $course    = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user      = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperId);
        }

        $isTeacher = $this->getCourseService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/testpaper-check/result-list.html.twig', array(
            'course'    => $course,
            'courseSet' => $courseSet,
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
