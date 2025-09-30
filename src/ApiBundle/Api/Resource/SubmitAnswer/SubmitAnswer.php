<?php

namespace ApiBundle\Api\Resource\SubmitAnswer;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentException;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Testpaper\ExerciseException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class SubmitAnswer extends AbstractResource
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
        $assessment = $this->getAssessmentService()->getAssessment($assessmentResponse['assessment_id']);
        if (empty($assessment) || (!empty($assessment['parent_id']) && empty($this->getAssessmentService()->getAssessment($assessment['parent_id'])))) {
            throw AssessmentException::ASSESSMENT_DELETED();
        }
        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if (empty($answerRecord) || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $this->getAnswerService()->submitAnswer($assessmentResponse);
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
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

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }
}
