<?php 

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\HomeworkItemResultDao;

class HomeworkItemResultDaoImpl extends BaseDao implements HomeworkItemResultDao
{
	protected $table = 'homework_item_result';

	public function getHomeworkItemResult($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}
	
	public function getHomeworkItemResultByHomeworkIdAndStatus($homeworkId,$status)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ?  AND status = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($homeworkId,$status)) ? : null;
	}

	public function getHomeworkItemResultByHomeworkIdAndHomeworkResultIdAndQuestionId($homeworkId,$homeworkResultId,$questionId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ?  AND HomeworkResultId = ? AND questionId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($homeworkId,$homeworkResultId,$questionId)) ? : null;
	}

	public function addHomeworkItemResult($itemResult)
	{
        $affected = $this->getConnection()->insert($this->table, $itemResult);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert HomeworkItemResult error.');
        }
        return $this->getHomeworkItemResult($this->getConnection()->lastInsertId());
	}

	public function updateHomeworkItemResult($homeworkId,$homeworkResultId,$questionId,$fields)
	{
		$homeworkItemResult = $this->getHomeworkItemResultByHomeworkIdAndHomeworkResultIdAndQuestionId($homeworkId,$homeworkResultId,$questionId);

        $this->getConnection()->update($this->table, $fields, array('id' => $homeworkItemResult['id']));
        return true;
	}

	public function findHomeworkItemsResultsbyHomeworkId($homeworkId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? ";
        return $this->getConnection()->fetchAll($sql,array($homeworkId)) ? : array();
	}

	public function findHomeworkItemsResultsbyHomeworkIdAndUserId($homeworkId,$userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql,array($homeworkId,$userId)) ? : array();
	}

}