<?php

namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerReviewedQuestionDao;

class AnswerReviewedQuestionDaoImpl extends GeneralDaoImpl implements AnswerReviewedQuestionDao
{
    protected $table = 'biz_answer_reviewed_question';

    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->findByFields(['answer_record_id' => $answerRecordId]);
    }

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId)
    {
        return $this->getByFields([
            'answer_record_id' => $answerRecordId,
            'question_id' => $questionId,
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
            'orderbys' => [],
            'conditions' => [
                'answer_record_id = :answer_record_id',
                'is_reviewed = :is_reviewed',
            ],
        ];
    }
}
