<?php

namespace Biz\ItemBank\Dao\Impl;

use Biz\ItemBank\Dao\AssessmentExerciseRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AssessmentExerciseRecordDaoImpl extends GeneralDaoImpl implements AssessmentExerciseRecordDao
{
    protected $table = 'item_bank_assessment_exercise_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
