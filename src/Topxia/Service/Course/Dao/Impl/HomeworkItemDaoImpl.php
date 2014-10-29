<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\HomeworkItemDao;

class HomeworkItemDaoImpl extends BaseDao implements HomeworkItemDao
{
    protected $table = 'homework_item';

    public function getItem($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return  $this->getConnection()->fetchAssoc($sql,array($id)) ? : array();
    }

    public function addItem($items)
    {
        $affect = $this->getConnection()->insert($this->table,$items);
        if ($affect <= 0) {
            throw $this->createDaoException('insert homeworkItem error!');
        }
        return $this->getItem($this->getConnection()->lastInsertId());
    }

    public function deleteItem($id)
    {
        return $this->getConnection()->delete($this->table,array('id'=>$id));
    }

    public function findItemsByHomeworkId($homeworkId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE homeworkId = ?";
        return $this->getConnection()->fetchAll($sql,array($homeworkId)) ? : array();
    }
}
