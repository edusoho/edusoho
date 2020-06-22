<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\AssessmentExerciseRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AssessmentExerciseRecordDaoImpl extends GeneralDaoImpl implements AssessmentExerciseRecordDao
{
    protected $table = 'item_bank_assessment_exercise_record';

    public function getByAnswerRecordId($answerRecordId)
    {
        return $this->getByFields(['answerRecordId' => $answerRecordId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'userId = :userId',
                'moduleId = :moduleId',
            ],
        ];
    }
}
