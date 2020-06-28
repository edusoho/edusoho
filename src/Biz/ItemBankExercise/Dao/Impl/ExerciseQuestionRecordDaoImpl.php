<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseQuestionRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseQuestionRecordDaoImpl extends AdvancedDaoImpl implements ExerciseQuestionRecordDao
{
    protected $table = 'item_bank_exercise_question_record';

    public function findByUserIdAndModuleId($userId, $moduleId)
    {
        return $this->findByFields(['userId' => $userId, 'moduleId' => $moduleId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'itemBankId = :itemBankId',
                'questionId IN (:questionIds)',
                'itemId IN (:itemIds)',
            ],
        ];
    }
}
