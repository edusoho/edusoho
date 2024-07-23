<?php

namespace ApiBundle\Api\Resource\Exercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Constant\ActivityMediaType;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Testpaper\ExerciseException;
use Biz\Testpaper\Wrapper\AssessmentResponseWrapper;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Constant\AnswerRecordStatus;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;

class ExerciseResult extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $user = $this->getCurrentUser();

        $targetType = $request->request->get('targetType');
        $targetId = $request->request->get('targetId');

        $task = $this->getTaskService()->getTask($targetId);
        if (empty($task) || 'exercise' != $task['type']) {
            throw TaskException::NOTFOUND_TASK();
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);
        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        if (!$this->getExerciseActivityService()->isExerciseAssessment($exerciseId, $activity['ext'])) {
            throw ExerciseException::EXERCISE_NOTDO();
        }

        $answerScene = $this->getAnswerSceneService()->get($activity['ext']['answerSceneId']);
        $assessment = $this->getAssessmentService()->showAssessment($exerciseId);
        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($answerScene['id'], $user['id']);
        $testpaperWrapper = new TestpaperWrapper();

        if (empty($answerRecord) || AnswerRecordStatus::FINISHED == $answerRecord['status']) {
            if ('draft' == $assessment['status']) {
                throw ExerciseException::DRAFT_EXERCISE();
            }
            if ('closed' == $assessment['status']) {
                throw ExerciseException::CLOSED_EXERCISE();
            }
            if ($assessment['id'] == $answerRecord['assessment_id']) {
                try {
                    $assessment = $this->getExerciseActivityService()->createExerciseAssessment($activity);
                    $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);
                } catch (ItemException $e) {
                    if (ErrorCode::ITEM_NOT_ENOUGH == $e->getCode()) {
                        throw ExerciseException::LACK_QUESTION();
                    }
                }
            }

            $answerRecord = $this->getAnswerService()->startAnswer($answerScene['id'], $assessment['id'], $user['id']);
            $answerRecord = $this->getAnswerRecordService()->update($answerRecord['id'], ['exercise_mode' => $request->request->get('exerciseMode', ExerciseMode::SUBMIT_ALL)]);
        } elseif (AnswerRecordStatus::REVIEWING != $answerRecord['status']) {
            $answerRecord = $this->getAnswerService()->continueAnswer($answerRecord['id']);
        }

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
        $items = $testpaperWrapper->wrapTestpaperItems($assessment, $questionReports);
        $exerciseResult = $testpaperWrapper->wrapTestpaperResult($answerRecord, $assessment, $answerScene, $answerReport);
        $exerciseResult['items'] = array_values($items);
        $exerciseResult['courseId'] = $course['id'];

        if (ExerciseMode::SUBMIT_SINGLE == $answerRecord['exercise_mode']) {
            $reviewedCount = $this->getAnswerReviewedQuestionService()->countReviewedByAnswerRecordId($answerRecord['id']);
            $submittedQuestions = $this->getAnswerService()->getSubmittedQuestions($answerRecord['id']);
        }
        $exerciseResult['reviewedCount'] = $reviewedCount ?? 0;
        $exerciseResult['submittedQuestions'] = $submittedQuestions ?? [];

        return $exerciseResult;
    }

    public function update(ApiRequest $request, $exerciseId, $exerciseResultId)
    {
        $user = $this->getCurrentUser();

        $data = $request->request->all();
        $exerciseRecord = $this->getAnswerRecordService()->get($exerciseResultId);

        if ($exerciseRecord['user_id'] != $user['id']) {
            throw ExerciseException::FORBIDDEN_ACCESS_EXERCISE();
        }

        $wrapper = new AssessmentResponseWrapper();
        $assessment = $this->getAssessmentService()->showAssessment($exerciseRecord['assessment_id']);
        $assessmentResponse = $wrapper->wrap($data, $assessment, $exerciseRecord);
        $answerRecord = $this->getAnswerService()->submitAnswer($assessmentResponse);
        $answerReport = $this->getAnswerReportService()->getSimple($answerRecord['answer_report_id']);
        $scene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $testpaperWrapper = new TestpaperWrapper();

        return $testpaperWrapper->wrapTestpaperResult($answerRecord, $assessment, $scene, $answerReport);
    }

    public function get(ApiRequest $request, $exerciseId, $exerciseResultId)
    {
        $user = $this->getCurrentUser();

        $exerciseRecord = $this->getAnswerRecordService()->get($exerciseResultId);
        if (empty($exerciseRecord)) {
            throw ExerciseException::NOTFOUND_RESULT();
        }

        $exercise = $this->getAssessmentService()->getAssessment($exerciseRecord['assessment_id']);
        if (empty($exercise) || '0' != $exercise['displayable']) {
            throw ExerciseException::NOTFOUND_EXERCISE();
        }

        $scene = $this->getAnswerSceneService()->get($exerciseRecord['answer_scene_id']);
        $activity = $this->getActivityService()->getActivityByAnswerSceneIdAndMediaType($scene['id'], ActivityMediaType::EXERCISE);

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activity['fromCourseId']);
        if (!$canTakeCourse) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        if (AnswerRecordStatus::DOING === $exerciseRecord['status'] && ($exerciseRecord['user_id'] != $user['id'])) {
            throw ExerciseException::FORBIDDEN_ACCESS_EXERCISE();
        }

        $testpaperWrapper = new TestpaperWrapper();
        $assessment = $this->getAssessmentService()->showAssessment($exercise['id']);
        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($exerciseRecord['id']);
        $answerReport = $this->getAnswerReportService()->getSimple($exerciseRecord['answer_report_id']);
        $exerciseResult = $testpaperWrapper->wrapTestpaperResult($exerciseRecord, $exercise, $scene, $answerReport);
        $exerciseResult['items'] = array_values($testpaperWrapper->wrapTestpaperItems($assessment, $questionReports));
        $exerciseResult['items'] = $testpaperWrapper->wrapAIAnalysis($exerciseResult['items']);
        $exerciseResult['items'] = $this->fillItems($exerciseResult['items'], $questionReports);
        $exerciseResult['rightRate'] = $this->getRightRate($exerciseResult['items']);

        return $exerciseResult;
    }

    protected function getRightRate($items)
    {
        $subjectivityNum = $rightNum = $num = 0;

        foreach ($items as $item) {
            ++$num;

            if (isset($item['testResult']) && 'right' == $item['testResult']['status']) {
                ++$rightNum;
            }

            if ('essay' == $item['type']) {
                ++$subjectivityNum;
            }

            if ('material' == $item['type'] && !empty($item['subs'])) {
                --$num;
                foreach ($item['subs'] as $subItem) {
                    ++$num;

                    if ('essay' == $subItem['type']) {
                        ++$subjectivityNum;
                    }

                    if (isset($subItem['testResult']) && 'right' == $subItem['testResult']['status']) {
                        ++$rightNum;
                    }
                }
            }
        }

        return ($num - $subjectivityNum) ? intval($rightNum / ($num - $subjectivityNum) * 100 + 0.5) : 0;
    }

    protected function fillItems($items, $questionReports)
    {
        $questionReports = ArrayToolkit::index($questionReports, 'question_id');
        foreach ($items as &$item) {
            if (isset($item['subs'])) {
                foreach ($item['subs'] as &$sub) {
                    $sub['testResult'] = isset($questionReports[$sub['id']]) ? $sub['testResult'] : null;
                }
            } else {
                $item['testResult'] = isset($questionReports[$item['id']]) ? $item['testResult'] : null;
            }
        }

        return $items;
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

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->service('Activity:ExerciseActivityService');
    }

    /**
     * @return AnswerReviewedQuestionService
     */
    protected function getAnswerReviewedQuestionService()
    {
        return $this->service('ItemBank:Answer:AnswerReviewedQuestionService');
    }
}
