<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Symfony\Component\HttpFoundation\Request;

class TestpaperController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity, $preview = 0)
    {
        if ($preview) {
            return $this->previewTestpaper($activity['id'], $activity['fromCourseId']);
        }

        $user = $this->getUser();
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($testpaperActivity['mediaId'], $activity['mediaType']);

        if (!$testpaper) {
            return $this->render('activity/testpaper/preview.html.twig', [
                'paper' => null,
            ]);
        }

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        if (!$testpaperResult || ('doing' == $testpaperResult['status'] && !$testpaperResult['updateTime']) || 'open' != $testpaper['status']) {
            return $this->render('activity/testpaper/show.html.twig', [
                'activity' => $activity,
                'testpaperActivity' => $testpaperActivity,
                'testpaperResult' => $testpaperResult,
                'testpaper' => $testpaper,
                'courseId' => $activity['fromCourseId'],
            ]);
        } elseif ('finished' === $testpaperResult['status']) {
            return $this->forward('AppBundle:Testpaper/Testpaper:showResult', [
                'resultId' => $testpaperResult['id'],
            ]);
        }

        return $this->forward('AppBundle:Testpaper/Testpaper:doTestpaper', [
            'testId' => $testpaperActivity['mediaId'],
            'lessonId' => $activity['id'],
        ]);
    }

    public function previewAction(Request $request, $task)
    {
        return $this->previewTestpaper($task['activityId'], $task['courseId']);
    }

    public function previewTestpaper($id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);
        $assessment = $this->getAssessmentService()->showAssessment($testpaperActivity['mediaId']);

        return $this->render('activity/testpaper/preview.html.twig', [
            'assessment' => $assessment,
        ]);
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $activity = $this->getActivityService()->getActivity($id);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperActivity) {
            $testpaperActivity['testpaperMediaId'] = $testpaperActivity['mediaId'];
            unset($testpaperActivity['mediaId']);
        }
        $activity = array_merge($activity, $testpaperActivity);

        $testpapers = $this->findCourseTestpapers($course);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : [];

        return $this->render('activity/testpaper/modal.html.twig', [
            'activity' => $activity,
            'testpapers' => $testpapers,
            'features' => $features,
            'courseId' => $activity['fromCourseId'],
            'course' => $course,
        ]);
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $testpapers = $this->findCourseTestpapers($course);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : [];

        return $this->render('activity/testpaper/modal.html.twig', [
            'testpapers' => $testpapers,
            'features' => $features,
            'course' => $course,
        ]);
    }

    public function finishConditionAction(Request $request, $activity)
    {
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        return $this->render('activity/testpaper/finish-condition.html.twig', [
            'testpaperActivity' => $testpaperActivity,
        ]);
    }

    public function learnDataDetailAction(Request $request, $task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $conditions = [
            'courseTaskId' => $task['id'],
        ];

        $paginator = new Paginator(
            $request,
            $this->getTaskResultService()->countTaskResults($conditions),
            20
        );

        $taskResults = $this->getTaskResultService()->searchTaskResults(
            $conditions,
            ['createdTime' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($taskResults, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $testpaperResults = $this->getTestpaperResults($activity, $userIds);

        return $this->render('activity/testpaper/learn-data-detail-modal.html.twig', [
            'task' => $task,
            'taskResults' => $taskResults,
            'users' => $users,
            'testpaperResults' => $testpaperResults,
            'paginator' => $paginator,
        ]);
    }

    protected function getTestpaperResults($activity, $userIds)
    {
        $testpaperResults = [];
        $answerRecords = $this->getAnswerRecords($activity['ext']['answerScene']['id'], $userIds);

        foreach ($answerRecords as $userId => $userAnswerRecords) {
            $userFirstRecord = $userAnswerRecords[0];
            $scores = ArrayToolkit::column($userAnswerRecords, 'score');
            $testpaperResults[$userId] = [
                'usedTime' => round($userFirstRecord['used_time'] / 60, 1),
                'firstScore' => $userFirstRecord['score'],
                'maxScore' => max($scores),
            ];
        }

        return $testpaperResults;
    }

    protected function getAnswerRecords($answerSceneId, $userIds)
    {
        $answerReports = $this->getAnswerReportService()->search(
            ['answer_scene_id' => $answerSceneId],
            [],
            0,
            $this->getAnswerReportService()->count(['answer_scene_id' => $answerSceneId]),
            ['score', 'user_id', 'answer_record_id']
        );
        $answerReports = ArrayToolkit::index($answerReports, 'answer_record_id');

        $conditions = [
            'answer_scene_id' => $answerSceneId,
            'user_ids' => $userIds,
            'status' => 'finished',
        ];
        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            [],
            0,
            $this->getAnswerRecordService()->count($conditions),
            ['user_id', 'used_time', 'id']
        );
        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['score'] = $answerReports[$answerRecord['id']]['score'];
        }
        $answerRecords = ArrayToolkit::group($answerRecords, 'user_id');

        return $answerRecords;
    }

    protected function findCourseTestpapers($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $conditions = [
            'courseSetId' => $course['courseSetId'],
            'status' => 'open',
            'type' => 'testpaper',
        ];

        if ($courseSet['parentId'] > 0 && $courseSet['locked']) {
            $conditions['copyIdGT'] = 0;
        }

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            ['createdTime' => 'DESC'],
            0,
            PHP_INT_MAX
        );

        return $testpapers;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }
}
