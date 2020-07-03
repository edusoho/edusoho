<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AssessmentExerciseDaoImpl extends AdvancedDaoImpl implements AssessmentExerciseDao
{
    protected $table = 'item_bank_assessment_exercise';

    public function findByModuleId($moduleId)
    {
        return $this->findByFields(['moduleId' => $moduleId]);
    }

    public function findByExerciseIdAndModuleId($exerciseId, $moduleId)
    {
        return $this->findByFields(['exerciseId' => $exerciseId, 'moduleId' => $moduleId]);
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
                'id in (:ids)',
                'exerciseId = :exerciseId',
                'moduleId = :moduleId',
                'assessmentId = :assessmentId',
            ],
        ];
    }
}
