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

    public function countByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerReviewedQuestionDao()->countByAnswerRecordId($answerRecordId);
    }

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId)
    {
        return $this->getAnswerReviewedQuestionDao()->getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);
    }

    public function createAnswerReviewedQuestion($answerReviewedQuestion)
    {
        $reviewedQuestion = $this->getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);
        if ($reviewedQuestion) {
            return $reviewedQuestion;
        }

        return $this->getAnswerReviewedQuestionDao()->create($answerReviewedQuestion);
    }

    /**
     * @return AnswerReviewedQuestionDao
     */
    protected function getAnswerReviewedQuestionDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerReviewedQuestionDao');
    }
}
