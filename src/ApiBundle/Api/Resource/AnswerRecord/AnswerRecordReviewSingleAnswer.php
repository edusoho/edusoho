<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;
use Biz\Common\CommonException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Topxia\Service\Common\ServiceKernel;

class AnswerRecordReviewSingleAnswer extends AbstractResource
{
    public function add(ApiRequest $request, $recordId)
    {
        $params = $request->request->all();
        $this->validateParams($params, $recordId);

        $questionReport = $this->getAnswerService()->reviewSingleAnswer($params, $recordId);

        $answerRecord = $this->getAnswerRecordService()->get($questionReport['answer_record_id']);
        $assessment = $this->getAssessmentService()->getAssessment($questionReport['assessment_id']);
        $reviewedCount = $this->getAnswerQuestionReportService()->count([
                'answer_record_id' => $recordId,
                'not_status' => AnswerQuestionReportService::STATUS_REVIEWING,
            ]);

        return [
            'status' => $questionReport['status'],
            'reviewedCount' => $reviewedCount,
            'totalCount' => $assessment['question_count'],
            'isAnswerFinished' => (AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) ? 1 : 0,
        ];
    }

    protected function validateParams($params, $recordId)
    {
        if (empty($params['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        $answerRecord = $this->getAnswerRecordService()->get($recordId);
        if (AnswerService::EXERCISE_MODE_SUBMIT_SINGLE != $answerRecord['exercise_mode']) {
            throw new AnswerException('非一题一答模式，不能批阅', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if ($answerRecord['assessment_id'] != $params['assessment_id']) {
            throw new InvalidArgumentException('assessment_id invalid.');
        }

        $sectionItems = $this->getAssessmentSectionItemDao()->findByAssessmentId($params['assessment_id']);
        if ($sectionItems['item_id'] != $params['item_id'] || $sectionItems['section_id'] != $params['section_id']) {
            throw CommonException::ERROR_PARAMETER();
        }

        $item = $this->getItemService()->getItemWithQuestions($params['item_id'], true);
        $questionId = ArrayToolkit::column($item['questions'], 'id');
        if (!in_array($params['question_id'], $questionId)) {
            throw CommonException::ERROR_PARAMETER();
        }
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->service('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return ServiceKernel::instance()->createDao('ItemBank:Assessment:AssessmentSectionItemDao');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->service('ItemBank:Item:ItemService');
    }
}
