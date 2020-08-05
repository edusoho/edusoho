<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use Biz\ItemBankExercise\Dao\ExerciseModuleDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ExerciseModuleDaoImpl extends GeneralDaoImpl implements ExerciseModuleDao
{
    protected $table = 'item_bank_exercise_module';

    public function findByExerciseId($exerciseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? ORDER BY seq ASC, id ASC;";

        return $this->db()->fetchAll($sql, [$exerciseId]);
    }

    public function findByExerciseIdAndType($exerciseId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE exerciseId = ? and type=? ORDER BY seq ASC, id ASC;";

        return $this->db()->fetchAll($sql, [$exerciseId, $type]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime', 'seq'],
            'conditions' => [
                'id IN (:ids)',
                'type IN (:types)',
                'exerciseId = :exerciseId',
                'type = :type',
            ],
        ];
    }
}
