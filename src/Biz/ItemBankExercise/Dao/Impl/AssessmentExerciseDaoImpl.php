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

    public function findByModuleIds($moduleIds)
    {
        return $this->findInField('moduleId', $moduleIds);
    }

    public function findByExerciseIdAndModuleId($exerciseId, $moduleId)
    {
        return $this->findByFields(['exerciseId' => $exerciseId, 'moduleId' => $moduleId]);
    }

    public function getByModuleIdAndAssessmentId($moduleId, $assessmentId)
    {
        return $this->getByFields([
            'moduleId' => $moduleId,
            'assessmentId' => $assessmentId,
        ]);
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

    public function getAssessmentCountGroupByExerciseId($ids)
    {
        $builder = $this->createQueryBuilder(['exerciseIds' => $ids])
            ->select('exerciseId, count(id) AS assessmentNum')
            ->groupBy('exerciseId');

        return $builder->execute()->fetchAll();
    }

    public function getByAssessmentId($assessmentId)
    {
        return $this->getByFields(['assessmentId' => $assessmentId]);
    }

    public function deleteByExerciseId($exerciseId)
    {
        return $this->db()->delete($this->table(), ['exerciseId' => $exerciseId]);
    }

    public function deleteByAssessmentId($assessmentId)
    {
        return $this->db()->delete($this->table(), ['assessmentId' => $assessmentId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'id in (:ids)',
                'exerciseId in (:exerciseIds)',
                'exerciseId = :exerciseId',
                'moduleId = :moduleId',
                'assessmentId = :assessmentId',
            ],
        ];
    }
}
