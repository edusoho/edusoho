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
		$sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? ORDER BY createdTime";
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
    	
	public function searchNotes($conditions, $orderBys, $start, $limit)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->select('*')
			->from($this->table, 'note')
			->andWhere('courseId = :courseId')
			->andWhere('lessonId = :lessonId')
			->andWhere('userId = :userId')
			->andWhere('type = :type')
			->andWhere('isStick = :isStick')
			->andWhere('isElite = :isElite')
			->andWhere('content LIKE :keywords')
			->setFirstResult($start)
			->setMaxResults($limit);
		foreach ($orderBys as $orderBy) {
			$builder->addOrderBy($orderBy[0], $orderBy[1]);
		}

		return $builder->execute()->fetchAll() ? : array();
	}
	
	public function searchNotesCount($conditions)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->select('count(id)')
			->from($this->table, 'note')
			->andWhere('courseId = :courseId')
			->andWhere('lessonId = :lessonId')
			->andWhere('userId = :userId')
			->andWhere('type = :type')
			->andWhere('isStick = :isStick')
			->andWhere('isElite = :isElite')
			->andWhere('content LIKE ":keywords"');
		return $builder->execute()->fetchColumn(0);
	}
}