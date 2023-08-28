<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerReviewedQuestionDao;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReviewedQuestionService;
use Codeages\Biz\ItemBank\BaseService;

class AnswerReviewedQuestionServiceImpl extends BaseService implements AnswerReviewedQuestionService
{
    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerReviewedQuestionDao()->findByAnswerRecordId($answerRecordId);
    }

    public function countReviewedByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerReviewedQuestionDao()->count(['answer_record_id' => $answerRecordId, 'is_reviewed' => 1]);
    }

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId)
    {
        return $this->getAnswerReviewedQuestionDao()->getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);
    }

    public function createAnswerReviewedQuestion($answerReviewedQuestion)
    {
        $reviewedQuestion = $this->getByAnswerRecordIdAndQuestionId($answerReviewedQuestion['answer_record_id'], $answerReviewedQuestion['question_id']);
        if ($reviewedQuestion) {
            return $reviewedQuestion;
        }

        return $this->getAnswerReviewedQuestionDao()->create($answerReviewedQuestion);
    }

    public function updateAnswerReviewedQuestion($id, $params)
    {
        return $this->getAnswerReviewedQuestionDao()->update($id, $params);
    }

    /**
     * @return AnswerReviewedQuestionDao
     */
    protected function getAnswerReviewedQuestionDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerReviewedQuestionDao');
    }
}
