<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class DoTestDaoImpl extends BaseDao
{
	protected $table = "test_result";

	public function addItemResults ($answers, $testId, $userId)
	{
		if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 3)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	array_push($answersForSQL, (int)$key, (int)$testId, (int)$userId, $value);
        }

		$sql = "INSERT INTO {$this->table} (`itemId`, `testId`, `userId`, `answer`) VALUES {$marks};";

		return $this->getConnection()->executeUpdate($sql, $answersForSQL);
	}

    //要不要给这三个字段加上索引呢
	public function updateItemResults ($answers, $testId, $userId)
	{
        //事务
		if(empty($answers)){ 
            return array(); 
        }
        $sql ='';
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	$sql .= "UPDATE {$this->table} set `answer` = ? WHERE `itemId` = ? AND `testId` = ? AND `userId` = ?;";
        	array_push($answersForSQL, $value, (int)$key, (int)$testId, (int)$userId); 
        }

        return $this->getConnection()->executeQuery($sql, $answersForSQL);
	}

	public function findTestResultsByItemIdAndTestId ($itemIds, $testId, $userId)
	{
		if(empty($itemIds)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($itemIds) - 1) . '?';

        $itemIds[] = $testId;
        $itemIds[] = $userId;

        $sql ="SELECT * FROM {$this->table} WHERE itemId IN ({$marks}) AND testId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql, $itemIds) ? : array();
	}

    public function findTestResultsByTestIdAndUserId ($testId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql, array($testId, $userId));
    }
}