<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordSubmitSingleAnswer extends AbstractResource
{
    const ONE_QUESTION_ONE_ANSWER = '1';

    public function add(ApiRequest $request, $recordId)
    {
        $params = $request->request->all();
        $this->validateParams($params, $recordId);
        $params = $this->trimResponse($params);

        $questionReport = $this->getAnswerService()->submitSingleAnswer($params);

        $item = $this->getItemService()->getItem($questionReport['item_id']);
        $question = $this->getItemService()->getQuestion($questionReport['question_id']);

        $answerRecord = $this->getAnswerRecordService()->get($questionReport['answer_record_id']);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);
        $assessment = $this->getAssessmentService()->getAssessment($questionReport['assessment_id']);

        $reviewedCount = $this->getAnswerQuestionReportService()->count(
            [
                'answer_record_id' => $params['answer_record_id'],
                'statusNo' => AnswerQuestionReportService::STATUS_REVIEWING,
            ]);

        return [
            'answer' => $question['answer'],
            'itemAnalysis' => $item['analysis'],
            'questionAnalysis' => $question['analysis'],
            'status' => $questionReport['status'],
            'manualMarking' => $answerScene['manual_marking'],
            'reviewedCount' => $reviewedCount,
            'totalCount' => $assessment['question_count'],
            'isAnswerFinished' => ('finish' == $answerRecord['status']) ? 1 : 0,
        ];
    }

    public function validateParams($params, $recordId)
    {
        if (empty($params['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        $answerRecord = $this->getAnswerRecordService()->get($recordId);

        if (empty($answerRecord) || $this->getCurrentUser()->getId() != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if (self::ONE_QUESTION_ONE_ANSWER != $answerRecord['exercise_mode']) {
            throw new AnswerException('非一题一答模式，不能保存', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if ($answerRecord['assessment_id'] != $params['assessment_id']) {
            throw new InvalidArgumentException('assessment_id invalid.');
        }

        if ($answerRecord['admission_ticket'] != $params['admission_ticket']) {
            throw new AnswerException('有新答题页面，请在新页面中继续答题', ErrorCode::ANSWER_NO_BOTH_DOING);
        }

        if (!in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_DOING, AnswerService::ANSWER_RECORD_STATUS_PAUSED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }
    }

    protected function trimResponse($params)
    {
        foreach ($params['response'] as &$response) {
            $response = trim($response);
        }

        return $params;
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->service('ItemBank:Answer:AnswerQuestionReportService');
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
}
