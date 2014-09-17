<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseNotePraiseDao;
use PDO;

class CourseNotePraiseDaoImpl extends BaseDao implements CourseNotePraiseDao
{
    protected $table = 'course_note_praise';
	
	public function getNotePraise($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function addNotePraise($notePraise)
	{
		$affected = $this->getConnection()->insert($this->table, $notePraise);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert notePraise error.');
        }
        return $this->getNotePraise($this->getConnection()->lastInsertId());
	}

	public function deleteNotePraiseByNoteIdAndUserId($noteId,$userId)
	{
		return $this->getConnection()->delete($this->table, array('noteId' => $noteId,'userId' => $userId));
	}

	public function findNotePraisesByUserId($userId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($userId));
	}

	public function findNotePraisesByNoteId($noteId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE noteId = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($noteId));
	}

}