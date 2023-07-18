<?php

namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerRandomSeqRecordDao;

class AnswerRandomSeqRecordDaoImpl extends GeneralDaoImpl implements AnswerRandomSeqRecordDao
{
    protected $table = 'biz_answer_random_seq_record';

    public function getByAnswerRecordId($answerRecordId)
    {
        return $this->getByFields(['answer_record_id' => $answerRecordId]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
            'serializes' => [
                'items_random_seq' => 'json',
                'options_random_seq' => 'json',
            ],
        ];
    }
}
