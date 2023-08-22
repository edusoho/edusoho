<?php
namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerQuestionReportDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AnswerQuestionReportDaoImpl extends AdvancedDaoImpl implements AnswerQuestionReportDao
{
    protected $table = 'biz_answer_question_report';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByAssessmentId($assessmentId)
    {
        $sql = "DELETE FROM {$this->table} WHERE assessment_id = ?";

        return $this->db()->executeUpdate($sql, [$assessmentId]);
    }

    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->findByFields(['answer_record_id' => $answerRecordId]);
    }

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId)
    {
        return $this->getByFields([
            'answer_record_id' => $answerRecordId,
            'question_id' => $questionId
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time'
            ],
            'orderbys' => [],
            'serializes' => [
                'response' => 'json',
                'revise' => 'json'
            ],
            'conditions' => [
                'answer_record_id = :answer_record_id',
                'answer_record_id IN (:answer_record_ids)',
                'status = :status',
                'status IN (:statues)',
                'status != (:statusNo)',
                'id IN (:ids)',
                'item_id = :question_id'
            ],
        ];
    }
}
