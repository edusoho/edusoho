<?php

namespace ApiBundle\Api\Resource\ContinueAnswer;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Activity\ActivityFilter;
use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use ApiBundle\Api\Resource\Assessment\AssessmentResponseFilter;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Testpaper\ExerciseException;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException;

class ContinueAnswer extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $assessmentResponse = $request->request->all();
        if (!empty($assessmentResponse['courseId'])) {
            $course = $this->getCourseService()->getCourse($assessmentResponse['courseId']);
            if ('0' == $course['canLearn']) {
                throw CourseException::CLOSED_COURSE();
            }
        }
        if (!empty($assessmentResponse['exerciseId'])) {
            $exercise = $this->getExerciseService()->get($assessmentResponse['exerciseId']);
            if ('closed' == $exercise['status']) {
                throw ExerciseException::CLOSED_EXERCISE();
            }
        }
        $answerRecord = $this->getAnswerRecordService()->get($request->request->get('answer_record_id'));
        if (empty($answerRecord) || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $answerRecord = $this->getAnswerService()->continueAnswer($request->request->get('answer_record_id'));

        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerRecord['answer_scene_id']);
        $activityFilter = new ActivityFilter();
        $activityFilter->filter($activity);

        $user = $this->getCurrentUser();
        $activity['isOnlyStudent'] = $user['roles'] == ['ROLE_USER'];

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
        if (empty($assessment)) {
            throw AssessmentException::ASSESSMENT_NOTEXIST();
        }
        if ('0' != $assessment['parent_id']) {
            $assessmentParent = $this->getAssessmentService()->getAssessment($assessment['parent_id']);
            if (empty($assessmentParent)) {
                throw AssessmentException::ASSESSMENT_NOTEXIST();
            }
            if ('closed' == $assessmentParent['status']) {
                throw AssessmentException::ASSESSMENT_NOTOPEN();
            }
        }
        if ('open' !== $assessment['status']) {
            throw AssessmentException::ASSESSMENT_NOTOPEN();
        }
        $assessment = $this->getAnswerRandomSeqService()->shuffleItemsAndOptionsIfNecessary($assessment, $answerRecord['id']);

        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->filter($assessment);
        if (1 == $assessment['displayable']) {
            $this->removeAnalysisAndAnswer($assessment);
        }

        $assessmentResponse = $this->getAnswerService()->getAssessmentResponseByAnswerRecordId($answerRecord['id']);
        $assessmentResponseFilter = new AssessmentResponseFilter();
        $assessmentResponseFilter->filter($assessmentResponse);

        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        if (ExerciseMode::SUBMIT_SINGLE == $answerRecord['exercise_mode']) {
            $reviewedCount = $this->getAnswerReviewedQuestionService()->countReviewedByAnswerRecordId($answerRecord['id']);
            $submittedQuestions = $this->getAnswerService()->getSubmittedQuestions($answerRecord['id']);
        }

        return [
            'assessment' => $assessment,
            'assessment_response' => $assessmentResponse,
            'answer_scene' => $answerScene,
            'answer_record' => $answerRecord,
            'metaActivity' => empty($activity) ? (object) [] : $activity,
            'reviewedCount' => $reviewedCount ?? 0,
            'submittedQuestions' => $submittedQuestions ?? [],
        ];
    }

    private function removeAnalysisAndAnswer(&$assessment)
    {
        foreach ($assessment['sections'] as &$section) {
            foreach ($section['items'] as &$item) {
                foreach ($item['questions'] as &$question) {
                    $question['analysis'] = '';
                    $question['answer'] = [];
                }
            }
        }
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerRandomSeqService
     */
    protected function getAnswerRandomSeqService()
    {
        return $this->service('ItemBank:Answer:AnswerRandomSeqService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return AnswerReviewedQuestionService
     */
    protected function getAnswerReviewedQuestionService()
    {
        return $this->service('ItemBank:Answer:AnswerReviewedQuestionService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }
}
