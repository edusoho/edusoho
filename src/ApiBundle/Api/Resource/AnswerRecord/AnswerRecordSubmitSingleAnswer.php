<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Exception\AnswerException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AnswerRecordSubmitSingleAnswer extends AbstractResource
{
    const TEST_MODE = '0';

    const ONE_QUESTION_ONE_ANSWER = '1';

    public function add(ApiRequest $request, $recordId)
    {
        $params = $request->request->all();
        $params = $this->validateParams($params, $recordId);

        $questionReport = $this->getAnswerQuestionReportService()->getByAnswerRecordIdAndItemId($recordId, $params['item_id']);
        $answerRecord = $this->getAnswerRecordService()->get($params['answer_record_id']);
        $answerScene = $this->getAnswerSceneService()->get($answerRecord['answer_scene_id']);

        $itemInfo = $this->getItemService()->getItemWithQuestion($params['item_id']);

        $isAnswerFinished = $this->canFinishedAnswer($recordId);

        return [
            'answer' => $itemInfo['question']['answer'],
            'itemAnalysis' => $itemInfo['analysis'],
            'questionAnalysis' => $itemInfo['question']['analysis'],
            'status' => $questionReport['status'],
            'manualMarking' => $answerScene['manual_marking'],
            'reviewedCount' => 1,
            'totalCount' => 1,
            'isAnswerFinished' => $isAnswerFinished ? 1 : 0,
        ];
    }

    public function validateParams($params, $recordId)
    {
        if (empty($params['exercise_mode']) || (self::ONE_QUESTION_ONE_ANSWER != $params['exercise_mode'])) {
            throw new AnswerException('非一题一答模式，不能保存', ErrorCode::EXERCISE_MODE_ERROR);
        }

        if (empty($assessmentResponse['admission_ticket'])) {
            throw new AnswerException('答题保存功能已升级，请更新客户端版本', ErrorCode::ANSWER_OLD_VERSION);
        }

        $answerRecord = $this->getAnswerRecordService()->get($params['answer_record_id']);
        if (empty($answerRecord) || $answerRecord['id'] != $recordId || $this->getCurrentUser()['id'] != $answerRecord['user_id']) {
            throw new AnswerException('找不到答题记录.', ErrorCode::ANSWER_RECORD_NOTFOUND);
        }

        if ($answerRecord['assessment_id'] != $params['assessment_id']) {
            throw $this->createInvalidArgumentException('assessment_id invalid.');
        }

        if ($answerRecord['admission_ticket'] != $params['admission_ticket']) {
            throw new AnswerException('有新答题页面，请在新页面中继续答题', ErrorCode::ANSWER_NO_BOTH_DOING);
        }

        if (!in_array($answerRecord['status'], [AnswerService::ANSWER_RECORD_STATUS_DOING, AnswerService::ANSWER_RECORD_STATUS_PAUSED])) {
            throw new AnswerException('你已提交过答题，当前页面无法重复提交', ErrorCode::ANSWER_NODOING);
        }

        foreach ($params['response'] as &$response) {
            $response = trim($response);
        }

        return $params;
    }

    protected function canFinishedAnswer($recordId)
    {
        $answerQuestionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($recordId);
        $answerQuestionReports = ArrayToolkit::group($answerQuestionReports, 'status');

        return empty($answerQuestionReports[AnswerQuestionReportService::STATUS_REVIEWING]);
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
}
