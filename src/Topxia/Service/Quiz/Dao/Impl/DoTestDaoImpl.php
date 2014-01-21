<?php

namespace Topxia\Service\Quiz\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class DoTestDaoImpl extends BaseDao
{
	protected $table = "test_item_result";

	public function addItemAnswers ($answers, $testPaperId, $testPaperResultId, $userId)
	{
		if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 4)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
        	array_push($answersForSQL, (int)$testPaperId, (int)$testPaperResultId, (int)$userId, (int)$key, $value);
        }

		$sql = "INSERT INTO {$this->table} (`testId`, `testPaperResultId`, `userId`, `questionId`, `answer`) VALUES {$marks};";

		return $this->getConnection()->executeUpdate($sql, $answersForSQL);
	}

    public function addItemResults ($answers, $testId, $testPaperResultId, $userId)
    {
        if(empty($answers)){ 
            return array(); 
        }

        $mark = "(".str_repeat('?,', 6)."? )";
        $marks = str_repeat($mark.',', count($answers) - 1).$mark;
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
            array_push($answersForSQL, (int)$testId, (int)$testPaperResultId, (int)$userId, (int)$key, $value['status'], $value['score'], $value['answer']);
        }

        $sql = "INSERT INTO {$this->table} (`testId`, `testPaperResultId`, `userId`, `questionId`, `status`, `score`, `answer`) VALUES {$marks};";

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

    public function updateItemEssays ($answers, $testPaperResultId)
    {
        //事务
        if(empty($answers)){
            return array(); 
        }
        $sql ='';
        $answersForSQL = array();
        foreach ($answers as $key => $value) {
            $sql .= "UPDATE {$this->table} set `score` = ?, `teacherSay` = ?, `status` = ? WHERE `questionId` = ? AND `testPaperResultId` = ?;";
            array_push($answersForSQL, $value['score'], $value['teacherSay'], $value['status'], (int)$key, (int)$testPaperResultId); 
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

    public function findRightItemCountByTestPaperResultId ($testPaperResultId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE testPaperResultId = ? AND status = 'right' ";
        return $this->getConnection()->fetchColumn($sql, array($testPaperResultId));
    }

    public function findWrongResultByUserId($id, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? AND `status` in ('wrong') LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }

    public function findWrongResultCountByUserId ($id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `userId` = ? AND `status` in ('wrong')";
        return $this->getConnection()->fetchColumn($sql, array($id));
    }

    
}