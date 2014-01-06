<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class DoTestDaoImpl extends BaseDao
{
	protected $table = "test_result";

	public function addItemAnswers ($answers, $testId, $userId)
	{
		if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 3)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	array_push($answersForSQL, (int)$testId, (int)$userId, (int)$key, $value);
        }

		$sql = "INSERT INTO {$this->table} (`testId`, `userId`, `questionId`, `answer`) VALUES {$marks};";

		return $this->getConnection()->executeUpdate($sql, $answersForSQL);
	}

    public function addItemResults ($answers, $testId, $userId)
    {
        if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 5)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
            array_push($answersForSQL, (int)$testId, (int)$userId, (int)$key, $value['status'], $value['score'], $value['answer']);
        }

        $sql = "INSERT INTO {$this->table} (`testId`, `userId`, `questionId`, `status`, `score`, `answer`) VALUES {$marks};";

        return $this->getConnection()->executeUpdate($sql, $answersForSQL);
    }

    //要不要给这三个字段加上索引呢
	public function updateItemAnswers ($answers, $testId, $userId)
	{
        //事务
		if(empty($answers)){ 
            return array(); 
        }
        $sql ='';
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	$sql .= "UPDATE {$this->table} set `answer` = ? WHERE `questionId` = ? AND `testId` = ? AND `userId` = ?;";
        	array_push($answersForSQL, $value, (int)$key, (int)$testId, (int)$userId); 
        }

        return $this->getConnection()->executeQuery($sql, $answersForSQL);
	}

    public function updateItemResults ($answers, $testId, $userId)
    {
        //事务
        if(empty($answers)){ 
            return array(); 
        }
        $sql ='';
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
            $sql .= "UPDATE {$this->table} set `status` = ?, `score` = ?, `answer` = ? WHERE `questionId` = ? AND `testId` = ? AND `userId` = ?;";
            array_push($answersForSQL, $value['status'], $value['score'], $value['answer'], (int)$key, (int)$testId, (int)$userId); 
        }

        return $this->getConnection()->executeQuery($sql, $answersForSQL);
    }

	public function findTestResultsByItemIdAndTestId ($questionIds, $testId, $userId)
	{
		if(empty($questionIds)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($questionIds) - 1) . '?';

        $questionIds[] = $testId;
        $questionIds[] = $userId;

        $sql ="SELECT * FROM {$this->table} WHERE questionId IN ({$marks}) AND testId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql, $questionIds) ? : array();
	}

    public function findTestResultsByTestIdAndUserId ($testId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql, array($testId, $userId));
    }
}