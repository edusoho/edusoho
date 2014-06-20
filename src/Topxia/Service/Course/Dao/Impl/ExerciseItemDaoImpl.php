<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ExerciseItemDao;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;

class ExerciseItemDaoImpl extends BaseDao implements ExerciseItemDao
{
    protected $table = 'exercise_item';

    public function getItem($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addItem($item)
    {
        $item = $this->getConnection()->insert($this->table, $item);
        if ($item <= 0) {
            throw $this->createDaoException('Insert item error.');
        }
        return $this->getItem($this->getConnection()->lastInsertId());
    }

    public function updateItem($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getItem($id);
    }

    public function deleteItem($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    } 

    public function deleteItemsByExerciseId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE ExerciseId = ? ";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

}