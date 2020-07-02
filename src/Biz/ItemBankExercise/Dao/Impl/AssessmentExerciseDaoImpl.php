<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AssessmentExerciseDaoImpl extends GeneralDaoImpl implements AssessmentExerciseDao
{
    protected $table = 'item_bank_assessment_exercise';

    public function findByModuleId($moduleId)
    {
        return $this->findByFields(['moduleId' => $moduleId]);
    }

    public function isAssessmentExercise($moduleId, $assessmentId, $exerciseId)
    {
        return $this->getByFields(
            [
                'exerciseId' => $exerciseId,
                'moduleId' => $moduleId,
                'assessmentId' => $assessmentId,
            ]
        );
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'exerciseId = :exerciseId',
                'moduleId = :moduleId',
                'assessmentId = :assessmentId',
            ],
        ];
    }
}
