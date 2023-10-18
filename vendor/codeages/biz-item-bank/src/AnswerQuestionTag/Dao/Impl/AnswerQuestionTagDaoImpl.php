<?php

namespace Codeages\Biz\ItemBank\AnswerQuestionTag\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\AnswerQuestionTag\Dao\AnswerQuestionTagDao;

class AnswerQuestionTagDaoImpl extends AdvancedDaoImpl implements AnswerQuestionTagDao
{
    protected $table = 'biz_answer_question_tag';

    public function getByAnswerRecordId($answerRecordId)
    {
        return $this->getByFields([
            'answer_record_id' => $answerRecordId,
        ]);
    }

    public function deleteByAnswerRecordId($answerRecordId)
    {
        $sql = "DELETE FROM {$this->table} WHERE answer_record_id = ? LIMIT 1";

        return $this->db()->executeUpdate($sql, [$answerRecordId]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time'
            ],
            'serializes' => [
                'tag_question_ids' => 'delimiter',
            ],
            'conditions' => [
                'answer_record_id = :answer_record_id',
            ]
        ];
    }
}