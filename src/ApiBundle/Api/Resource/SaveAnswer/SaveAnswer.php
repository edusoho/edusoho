<?php

namespace ApiBundle\Api\Resource\SaveAnswer;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Assessment\AssessmentException;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Testpaper\ExerciseException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;

class SaveAnswer extends AbstractResource
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
            $member = $this->getExerciseMemberService()->getExerciseStudent($assessmentResponse['exerciseId'], $this->getCurrentUser()['id']);
            if ('closed' == $exercise['status'] || 0 == $member['canLearn']) {
                throw ExerciseException::CLOSED_EXERCISE();
            }
        }

        $assessment = $this->getAssessmentService()->getAssessment($assessmentResponse['assessment_id']);
        if (empty($assessment) || (!empty($assessment['parent_id']) && empty($this->getAssessmentService()->getAssessment($assessment['parent_id'])))) {
            throw AssessmentException::ASSESSMENT_DELETED();
        }

        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (empty($assessmentResponse['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        if ($answerRecord['admission_ticket'] != $assessmentResponse['admission_ticket']) {
            throw new AnswerException('有新答题页面，请在新页面中继续答题', ErrorCode::ANSWER_NO_BOTH_DOING);
        }

        return $this->getAnswerService()->saveAnswer($assessmentResponse);
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
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
