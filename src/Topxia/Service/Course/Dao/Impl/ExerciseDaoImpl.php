<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ExerciseDao;

class ExerciseDaoImpl extends BaseDao implements ExerciseDao
{
    protected $table = 'exercise';

    public function getExercise($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addExercise($fields)
    {   
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert exercise error.');
        }
        return $this->getExercise($this->getConnection()->lastInsertId());
    }

}