<?php
namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerQuestionReportReviewedDao;

class AnswerReviewedQuestionDaoImpl extends AdvancedDaoImpl implements AnswerQuestionReportReviewedDao
{
    protected $table = 'biz_answer_reviewed_question';

    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->findByFields(['answer_record_id' => $answerRecordId]);
    }

    public function countByAnswerRecordId($answerRecordId)
    {
        return $this->count(['answer_record_id' => $answerRecordId]);
    }

    public function getByAnswerRecordIdAndQuestionId($recordId, $questionId)
    {
        return $this->getByFields([
            'answer_record_id' => $recordId,
            'question_id' => $questionId
            ]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
            'orderbys' => [],
            'serializes' => [
                'revise' => 'json'
            ],
            'conditions' => [
                'answer_record_id = :answer_record_id',
                'answer_record_id IN (:answer_record_ids)',
                'id IN (:ids)',
                'question_id = :question_id'
            ],
        ];
    }
}
