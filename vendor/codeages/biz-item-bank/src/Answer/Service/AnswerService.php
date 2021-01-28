<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerService
{
    const ANSWER_RECORD_STATUS_DOING = 'doing';

    const ANSWER_RECORD_STATUS_PAUSED = 'paused';

    const ANSWER_RECORD_STATUS_REVIEWING = 'reviewing';
    
    const ANSWER_RECORD_STATUS_FINISHED = 'finished';

    /**
     * 开始答题
     *
     * @param int $answerSceneId
     * @param int $userId
     * @param int $assessmentId
     * @return AnswerRecord
     */
    public function startAnswer($answerSceneId, $assessmentId, $userId);

    /**
     * 提交答题
     *
     * @param int $answerRecordId
     * @param array $responses
     * @return AnswerRecord
     */
    public function submitAnswer(array $assessmentResponse);

    /**
     * 暂停答题
     *
     * @param int $answerRecordId
     * @param array $responses
     * @return AnswerRecord
     */
    public function pauseAnswer(array $assessmentResponse);

    /**
     * 临时保存答案
     *
     * @param int $answerRecordId
     * @param array $responses
     * @return AssessmentResponse
     */
    public function saveAnswer(array $assessmentResponse);

    /**
     * 继续答题
     *
     * @param int $answerRecordId
     * @return AnswerRecord
     */
    public function continueAnswer($answerRecordId);

    /**
     * 获取答题内容
     *
     * @param int $answerRecordId
     * @return AssessmentResponse
     */
    public function getAssessmentResponseByAnswerRecordId($answerRecordId);
    
    /**
     * 手动批阅
     *
     * @param array $reviewReport
     * @return AnswerReport
     */
    public function review(array $reviewReport);
}
