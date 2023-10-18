<?php

namespace Codeages\Biz\ItemBank\AnswerQuestionTag\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\AnswerQuestionTag\Dao\AnswerQuestionTagDao;

class AnswerQuestionTagDaoImpl extends AdvancedDaoImpl implements AnswerQuestionTagDao
{
    protected $table = 'biz_answer_question_tag';

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