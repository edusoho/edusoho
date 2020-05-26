<?php

namespace AppBundle\Controller\Course;

use AppBundle\Controller\BaseController;
use Biz\Activity\ActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;

class TestpaperManageController extends BaseController
{
    public function checkAction(Request $request, $id, $answerRecordId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        return $this->forward('AppBundle:Testpaper/Manage:check', [
            'request' => $request,
            'answerRecordId' => $answerRecordId,
            'source' => 'course',
            'targetId' => $course['id'],
        ]);
    }

    /**
     * 仅作为8.0之前版本通知使用.
     */
    public function checkForwordAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        return $this->forward('AppBundle:Course/TestpaperManage:check', [
            'request' => $request,
            'resultId' => $result['id'],
            'source' => 'course',
            'targetId' => $result['courseId'],
        ]);
    }

    public function checkListAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();
        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/testpaper-check/check-list.html.twig', [
            'courseSet' => $courseSet,
            'course' => $course,
            'isTeacher' => $isTeacher,
        ]);
    }

    public function resultListAction(Request $request, $id, $testpaperId, $activityId)
    {
        $course = $this->getCourseService()->getCourse($id);
        $course = $this->getCourseService()->tryManageCourse($course['id'], $course['courseSetId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $user = $this->getUser();

        $testpaper = $this->getAssessmentService()->getAssessment($testpaperId);
        if (!$testpaper) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $activity = $this->getActivityService()->getActivity($activityId);
        if (!$activity) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        $isTeacher = $this->getCourseMemberService()->isCourseTeacher($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('course-manage/testpaper-check/result-list.html.twig', [
            'course' => $course,
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
            'isTeacher' => $isTeacher,
            'activityId' => $activity['id'],
            'activity' => $activity,
        ]);
    }

    public function resultGraphAction(Request $request, $id, $activityId)
    {
        $this->getCourseService()->tryManageCourse($id);
        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || $activity['fromCourseId'] != $id) {
            return $this->createMessageResponse('error', 'Activity not found');
        }

        if ('homework' == $activity['mediaType']) {
            $controller = 'AppBundle:HomeworkManage:resultGraph';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultGraph';
        }

        return $this->forward($controller, [
            'activityId' => $activityId,
        ]);
    }

    public function resultAnalysisAction(Request $request, $id, $activityId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $activity = $this->getActivityService()->getActivity($activityId);
        if (empty($activity) || !in_array($activity['mediaType'], ['homework', 'testpaper'])) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        if ('homework' == $activity['mediaType']) {
            $controller = 'AppBundle:HomeworkManage:resultAnalysis';
        } else {
            $controller = 'AppBundle:Testpaper/Manage:resultAnalysis';
        }

        return $this->forward($controller, [
            'activityId' => $activityId,
            'targetId' => $course['id'],
            'targetType' => 'course',
            'studentNum' => $course['studentNum'],
        ]);
    }

    public function resultNextCheckAction($id, $activityId)
    {
        $this->getCourseService()->tryManageCourse($id);
        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || $activity['fromCourseId'] != $id) {
            return $this->createMessageResponse('error', 'Activity not found');
        }

        $answerScene = $this->getAnswerSceneByActivity($activity);
        $answerRecord = $this->getAnswerRecordService()->getNextReviewingAnswerRecordByAnswerSceneId($answerScene['id']);

        if (empty($answerRecord)) {
            $route = $this->getRedirectRoute('list', $activity['mediaType']);

            return $this->redirect($this->generateUrl($route, ['id' => $id]));
        }

        $route = $this->getRedirectRoute('check', $activity['mediaType']);

        return $this->redirect($this->generateUrl($route, ['id' => $id, 'answerRecordId' => $answerRecord['id']]));
    }

    protected function getAnswerSceneByActivity($activity)
    {
        if ('testpaper' == $activity['mediaType']) {
            $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

            return $this->getAnswerSceneService()->get($testpaperActivity['answerSceneId']);
        }

        if ('homework' == $activity['mediaType']) {
            $homeworkActivity = $this->getHomeworkActivityService()->get($activity['mediaId']);

            return $this->getAnswerSceneService()->get($homeworkActivity['answerSceneId']);
        }
    }

    protected function getRedirectRoute($mode, $type)
    {
        $routes = [
            'list' => [
                'testpaper' => 'course_manage_testpaper_check_list',
                'homework' => 'course_manage_homework_check_list',
            ],
            'check' => [
                'testpaper' => 'course_manage_testpaper_check',
                'homework' => 'course_manage_homework_check',
            ],
        ];

        return $routes[$mode][$type];
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
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

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }
}
