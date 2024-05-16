<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Question\Traits\QuestionAIAnalysisTrait;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\ItemBank\Answer\Constant\AnswerRecordStatus;
use Codeages\Biz\ItemBank\Answer\Constant\ExerciseMode;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordSubmitSingleAnswer extends AbstractResource
{
    use QuestionAIAnalysisTrait;

    public function add(ApiRequest $request, $answerRecordId)
    {
        $params = $this->convertParams($answerRecordId, $request->request->all());
        $this->validateParams($answerRecordId, $params);
        $params['response'] = ArrayToolkit::trim($params['response']);

        $questionReport = $this->getAnswerService()->submitSingleAnswer($answerRecordId, $params);

        $assessment = $this->getAssessmentService()->getAssessment($questionReport['assessment_id']);
        $reviewedCount = $this->getAnswerReviewedQuestionService()->countReviewedByAnswerRecordId($questionReport['answer_record_id']);
        $answerRecord = $this->getAnswerRecordService()->get($questionReport['answer_record_id']);

        if ($reviewedCount >= $assessment['question_count']) {
            $this->getAnswerService()->finishAllSingleAnswer($answerRecord, 'submit');
        }
        $answerRecord = $this->getAnswerRecordService()->get($answerRecord['id']);
        $item = $this->getItemService()->getItemIncludeDeleted($questionReport['item_id']);
        $question = $this->getItemService()->getQuestionIncludeDeleted($questionReport['question_id']);

        return [
            'response' => $params['response'],
            'answer' => $question['answer'],
            'questionId' => $question['id'],
            'itemAnalysis' => $item['analysis'],
            'questionAnalysis' => $question['analysis'],
            'status' => $questionReport['status'],
            'manualMarking' => empty($questionReport['isReviewed']) ? 1 : 0,
            'reviewedCount' => $reviewedCount,
            'totalCount' => $assessment['question_count'],
            'isAnswerFinished' => (AnswerRecordStatus::FINISHED == $answerRecord['status']) ? 1 : 0,
            'aiAnalysisEnable' => $this->canGenerateAIAnalysis($question, $item),
        ];
    }

    private function convertParams($answerRecordId, $params)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord)) {
            return $params;
        }
        if ($answerRecord['assessment_id'] == $params['assessment_id']) {
            return $params;
        }
        $assessmentSnapshot = $this->getAssessmentService()->getAssessmentSnapshotBySnapshotAssessmentId($answerRecord['assessment_id']);
        if (empty($assessmentSnapshot) || $assessmentSnapshot['origin_assessment_id'] != $params['assessment_id']) {
            return $params;
        }
        $params['assessment_id'] = $answerRecord['assessment_id'];
        $params['section_id'] = $assessmentSnapshot['sections_snapshot'][$params['section_id']];

        return $params;
    }

    private function validateParams($answerRecordId, $params)
    {
        if (empty($params['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (ExerciseMode::SUBMIT_SINGLE != $answerRecord['exercise_mode']) {
            throw new AnswerException('非一题一答模式，不能保存', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if ($answerRecord['assessment_id'] != $params['assessment_id']) {
            throw new InvalidArgumentException('assessment_id invalid.');
        }

        if ($answerRecord['admission_ticket'] != $params['admission_ticket']) {
            throw new AnswerException('有新答题页面，请在新页面中继续答题', ErrorCode::ANSWER_NO_BOTH_DOING);
        }

        if (!in_array($answerRecord['status'], [AnswerRecordStatus::DOING, AnswerRecordStatus::PAUSED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }

        $sectionItem = $this->getSectionItemService()->getItemByAssessmentIdAndItemId($params['assessment_id'], $params['item_id']);
        if (empty($sectionItem) || $sectionItem['section_id'] != $params['section_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $question = $this->getItemService()->getQuestionIncludeDeleted($params['question_id']);
        if (empty($question) || $params['item_id'] != $question['item_id']) {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerReviewedQuestionService
     */
    protected function getAnswerReviewedQuestionService()
    {
        return $this->service('ItemBank:Answer:AnswerReviewedQuestionService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->service('ItemBank:Assessment:AssessmentSectionItemService');
    }
}
