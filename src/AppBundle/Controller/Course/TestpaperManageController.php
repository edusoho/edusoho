<?php

namespace AppBundle\Controller\Course;

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
        $course = $this->getCourseService()->tryManageCourse($id);

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
        $course = $this->getCourseService()->tryManageCourse($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();
        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/testpaper-check/check-list.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'isTeacher' => $isTeacher,
        ));
    }

    public function resultListAction(Request $request, $id, $testpaperId, $activityId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperId);
        }

        $activity = $this->getActivityService()->getActivity($activityId);
        if (!$activity) {
            throw $this->createResourceNotFoundException('activity', $activityId);
        }

        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/testpaper-check/result-list.html.twig', array(
            'course' => $course,
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
            'isTeacher' => $isTeacher,
            'activityId' => $activity['id'],
        ));
    }

    public function resultGraphAction(Request $request, $id, $activityId)
    {
        $this->getCourseService()->tryManageCourse($id);
        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || $activity['fromCourseId'] != $id) {
            return $this->createMessageResponse('error', 'Activity not found');
        }

        if ($activity['mediaType'] == 'homework') {
            $controller = 'AppBundle:HomeworkManage:resultGraph';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultGraph';
        }

        return $this->forward($controller, array(
            'activityId' => $activityId,
        ));
    }

    public function resultAnalysisAction(Request $request, $id, $activityId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

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

    public function resultNextCheckAction($id, $activityId)
    {
        $this->getCourseService()->tryManageCourse($id);
        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || $activity['fromCourseId'] != $id) {
            return $this->createMessageResponse('error', 'Activity not found');
        }

        $checkResult = $this->getTestpaperService()->getNextReviewingResult(array($id), $activity['id'], $activity['mediaType']);

        if (empty($checkResult)) {
            return $this->redirect($this->generateUrl('course_manage_'.$activity['mediaType'].'_check_list', array('id' => $id)));
        }

        return $this->redirect($this->generateUrl('course_manage_'.$activity['mediaType'].'_check', array('id' => $id, 'resultId' => $checkResult['id'])));
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
