<?php

namespace Custom\Service\Homework\Dao\Impl;

use Homework\Service\Homework\Dao\Impl\HomeworkItemResultDaoImpl as BaseResultItemDao;
use Custom\Service\Homework\Dao\ResultItemDao;

class ResultItemDaoImpl extends BaseResultItemDao implements ResultItemDao
{
    public function findItemsByResultId($resultId){
        $sql = "SELECT * FROM {$this->table} WHERE homeworkResultId = ?";
        $items= $this->getConnection()->fetchAll($sql, array($resultId));

        return $this->createSerializer()->unserializes($items, array(
	        'answer' => 'json',
	    )) ? : null;
    }

    public function findItemResultsbyUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = {$userId} ;";
        $items = $this->getConnection()->fetchAll($sql, array($userId));
        return $this->createSerializer()->unserializes($items, array(
	        'answer' => 'json',
	    )) ? : null;
    }
}