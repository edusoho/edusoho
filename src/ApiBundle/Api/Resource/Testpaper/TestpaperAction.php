<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Testpaper\TestpaperException;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Biz\User\UserException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class TestpaperAction extends AbstractResource
{
    /**
     * @param $request
     * @param $id
     * action "do/redo"
     * post params: targetType targetId
     */
    public function add(ApiRequest $request, $id)
    {
        $action = $request->request->get('action');
        $assessment = $this->getAssessmentService()->getAssessment($id);
        $method = $action.'Testpaper';
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        return $this->$method($request, $assessment);
    }

    protected function doTestpaper(ApiRequest $request, $assessment)
    {
        $targetType = $request->request->get('targetType'); // => task
        $targetId = $request->request->get('targetId'); // => taskId

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw UserException::UN_LOGIN();
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        if (empty($assessment)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $activity;
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $scene = $this->getAnswerSceneService()->get($testpaperActivity['answerSceneId']);
        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($testpaperActivity['answerSceneId'], $user['id']);
        if (empty($answerRecord)) {
            if ('draft' == $assessment['status']) {
                throw TestpaperException::DRAFT_TESTPAPER();
            }
            if ('closed' == $assessment['status']) {
                throw TestpaperException::CLOSED_TESTPAPER();
            }

            $answerRecord = $this->getAnswerService()->startAnswer($scene['id'], $assessment['id'], $user['id']);
        }

        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);

        $testpaperWrapper = new TestpaperWrapper();
        $items = ArrayToolkit::groupIndex($testpaperWrapper->wrapTestpaperItems($assessment, $questionReports), 'type', 'id');
        $testpaper = $testpaperWrapper->wrapTestpaper($assessment, $scene);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return [
            'testpaperResult' => $testpaperWrapper->wrapTestpaperResult($answerRecord, $assessment, $scene, $answerReport),
            'testpaper' => $testpaper,
            'items' => $items,
            'isShowTestResult' => 1,
        ];
    }

    protected function redoTestpaper(ApiRequest $request, $assessment)
    {
        $targetType = $request->request->get('targetType'); // => task
        $targetId = $request->request->get('targetId'); // => taskId

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw UserException::UN_LOGIN();
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        if (empty($assessment)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        if ('draft' == $assessment['status']) {
            throw TestpaperException::DRAFT_TESTPAPER();
        }

        if ('closed' == $assessment['status']) {
            throw TestpaperException::CLOSED_TESTPAPER();
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $activity;
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $scene = $this->getAnswerSceneService()->get($testpaperActivity['answerSceneId']);
        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($scene['id'], $user['id']);
        $answerReport = $this->getAnswerReportService()->get($answerRecord['answer_report_id']);

        if ($scene['do_times'] && $answerRecord && 'finished' == $answerRecord['status']) {
            throw TestpaperException::FORBIDDEN_RESIT();
        } elseif ($scene['redo_interval'] && $answerReport) {
            $nextDoTime = $answerReport['review_time'] + $scene['redo_interval'] * 60;
            if ($nextDoTime > time()) {
                throw TestpaperException::REDO_INTERVAL_EXIST();
            }
        }

        if (!$answerRecord || ($answerRecord && 'finished' == $answerRecord['status'])) {
            $answerRecord = $this->getAnswerService()->startAnswer($scene['id'], $assessment['id'], $user['id']);
            $answerReport = [];
        }

        $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $testpaperWrapper = new TestpaperWrapper();
        $items = ArrayToolkit::groupIndex($testpaperWrapper->wrapTestpaperItems($assessment, $questionReports), 'type', 'id');
        $testpaper = $testpaperWrapper->wrapTestpaper($assessment, $scene);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return [
            'testpaperResult' => $testpaperWrapper->wrapTestpaperResult($answerRecord, $assessment, $scene, $answerReport),
            'testpaper' => $testpaper,
            'items' => $items,
            'isShowTestResult' => 0,
        ];
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->service('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }
}
