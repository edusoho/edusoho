<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class DoTestDaoImpl extends BaseDao
{
	protected $table = "test_result";

	public function addItemAnswers ($answers, $testPaperResultId, $userId)
	{
		if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 3)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	array_push($answersForSQL, (int)$testPaperResultId, (int)$userId, (int)$key, $value);
        }

		$sql = "INSERT INTO {$this->table} (`testPaperResultId`, `userId`, `questionId`, `answer`) VALUES {$marks};";

		return $this->getConnection()->executeUpdate($sql, $answersForSQL);
	}

    public function addItemResults ($answers, $testPaperResultId, $userId)
    {
        if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 5)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
            array_push($answersForSQL, (int)$testPaperResultId, (int)$userId, (int)$key, $value['status'], $value['score'], $value['answer']);
        }

        $sql = "INSERT INTO {$this->table} (`testPaperResultId`, `userId`, `questionId`, `status`, `score`, `answer`) VALUES {$marks};";

        return $this->getConnection()->executeUpdate($sql, $answersForSQL);
    }

    //要不要给这三个字段加上索引呢
	public function updateItemAnswers ($answers, $testPaperResultId)
	{
        //事务
		if(empty($answers)){
            return array(); 
        }
        $sql ='';
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	$sql .= "UPDATE {$this->table} set `answer` = ? WHERE `questionId` = ? AND `testPaperResultId` = ?;";
        	array_push($answersForSQL, $value, (int)$key, (int)$testPaperResultId); 
        }

        return $this->getConnection()->executeQuery($sql, $answersForSQL);
	}

    public function updateItemResults ($answers, $testPaperResultId)
    {
        //事务
        if(empty($answers)){ 
            return array(); 
        }
        $sql ='';
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
            $sql .= "UPDATE {$this->table} set `status` = ?, `score` = ? WHERE `questionId` = ? AND `testPaperResultId` = ?;";
            array_push($answersForSQL, $value['status'], $value['score'], (int)$key, (int)$testPaperResultId); 
        }

        return $this->getConnection()->executeQuery($sql, $answersForSQL);
    }

	public function findTestResultsByItemIdAndTestId ($questionIds, $testPaperResultId)
	{
		if(empty($questionIds)){ 
            return array(); 
        }
        $marks = str_repeat('?,', count($questionIds) - 1) . '?';

        $questionIds[] = $testPaperResultId;

        $sql ="SELECT * FROM {$this->table} WHERE questionId IN ({$marks}) AND testPaperResultId = ?";
        return $this->getConnection()->fetchAll($sql, $questionIds) ? : array();
	}

    public function findTestResultsByTestPaperResultId ($testPaperResultId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE testPaperResultId = ?";
        return $this->getConnection()->fetchAll($sql, array($testPaperResultId));
    }
}