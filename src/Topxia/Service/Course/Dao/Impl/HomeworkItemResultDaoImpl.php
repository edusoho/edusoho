<?php 

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\HomeworkItemResultDao;

class HomeworkItemResultDaoImpl extends BaseDao implements HomeworkItemResultDao
{
	protected $table = 'homework_item_result';

	public function getItemResult($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}
	
	public function getItemResultByHomeworkIdAndStatus($homeworkId,$status)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ?  AND status = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($homeworkId,$status)) ? : null;
	}

	public function getItemResultByResultIdAndQuestionId($resultId,$questionId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkResultId = ?  AND questionId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($resultId,$questionId)) ? : null;
	}

	public function addItemResult($itemResult)
	{
        $affected = $this->getConnection()->insert($this->table, $itemResult);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert HomeworkItemResult error.');
        }
        return $this->getItemResult($this->getConnection()->lastInsertId());
	}

	public function updateItemResult($id,$fields)
	{
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return true;
	}

	public function findItemResultsbyHomeworkId($homeworkId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? ";
        return $this->getConnection()->fetchAll($sql,array($homeworkId)) ? : array();
	}

	public function findItemResultsbyHomeworkIdAndUserId($homeworkId,$userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? AND userId = ?";
        return $this->getConnection()->fetchAll($sql,array($homeworkId,$userId)) ? : array();
	}

}