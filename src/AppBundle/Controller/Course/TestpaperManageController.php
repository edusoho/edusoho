<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\TestpaperActivityService;

class TestpaperManageController extends BaseController
{
    public function checkAction(Request $request, $id, $resultId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);

        return $this->forward('AppBundle:Testpaper/Manage:check', array(
            'request' => $request,
            'resultId' => $resultId,
            'source' => 'course',
            'targetId' => $course['id'],
        ));
    }

    /**
     * 仅作为8.0之前版本通知使用.
     */
    public function checkForwordAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        return $this->forward('AppBundle:Course/TestpaperManage:check', array(
            'request' => $request,
            'resultId' => $result['id'],
            'source' => 'course',
            'targetId' => $result['courseId'],
        ));
    }

    public function checkListAction(Request $request, $id)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();
        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($course['id'], 'testpaper');

        $testpaperActivityIds = ArrayToolkit::column($activities, 'mediaId');

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByIds($testpaperActivityIds);

        return $this->render('course-manage/testpaper-check/check-list.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'isTeacher' => $isTeacher,
            'testpaperIds' => ArrayToolkit::column($testpaperActivities, 'mediaId'),
        ));
    }

    public function resultListAction(Request $request, $id, $testpaperId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperId);
        }

        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/testpaper-check/result-list.html.twig', array(
            'course' => $course,
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
            'isTeacher' => $isTeacher,
        ));
    }

    public function resultAnalysisAction(Request $request, $courseId, $activityId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $activity = $this->getActivityService()->getActivity($activityId);
        if (empty($activity) || !in_array($activity['mediaType'], array('homework', 'testpaper'))) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        if ($activity['mediaType'] == 'homework') {
            $controller = 'AppBundle:HomeworkManage:resultAnalysis';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultAnalysis';
        }

        return $this->forward($controller, array(
            'activityId' => $activityId,
            'targetId' => $course['id'],
            'targetType' => 'course',
            'studentNum' => $course['studentNum'],
        ));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
