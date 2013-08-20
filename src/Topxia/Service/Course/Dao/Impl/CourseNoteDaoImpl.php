<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseNoteDao;
use PDO;

class CourseNoteDaoImpl extends BaseDao implements CourseNoteDao
{
    protected $table = 'course_note';
	
	public function getNote($id)
	{
        return $this->fetch($id);
	}

	public function findNotesByUserIdAndCourseId($userId, $courseId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($userId, $courseId));
	}

	public function addNote($noteInfo)
	{
    	$id = $this->insert($noteInfo);
    	return $this->getNote($id);
	}

	public function updateNote($id,$noteInfo)
	{
		return $this->update($id,$noteInfo);
	}

	public function deleteNote($id)
	{
		return $this->delete($id);
	}

	public function getNoteByUserIdAndLessonId($userId,$lessonId)
	{
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? AND lessonId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId, $lessonId));
	}

    public function findNotesByUserIdAndStatus($userId, $status)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE userId = ? AND status = ?";
        return $this->getConnection()->fetchAll($sql, array($userId, $status));
    }
    	
	public function searchNotes($conditions, $orderBy, $start, $limit)
	{
		$builder = $this->createSearchNoteQueryBuilder($conditions)
			->select('*')
			->addOrderBy($orderBy[0], $orderBy[1])
			->setFirstResult($start)
			->setMaxResults($limit);

		return $builder->execute()->fetchAll() ? : array();
	}
	
	public function searchNoteCount($conditions)
	{
		$builder = $this->createSearchNoteQueryBuilder($conditions)
			->select('count(id)');

		return $builder->execute()->fetchColumn(0);
	}

	private function createSearchNoteQueryBuilder($conditions)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->from($this->table, 'note')
			->andWhere('courseId = :courseId')
			->andWhere('lessonId = :lessonId')
			->andWhere('userId = :userId')
			->andWhere('status = :status')
			->andWhere('isStick = :isStick')
			->andWhere('isElite = :isElite')
			->andWhere('content LIKE :keywords');

		return $builder;
	}

	public function getNoteCountByUserIdAndCourseId($userId, $courseId)
	{
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($userId, $courseId));
	}
}