<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseNoteDao;

class CourseNoteDaoImpl extends BaseDao implements CourseNoteDao
{
    protected $table = 'course_note';

    protected $allowedOrderFields = array(
        'createdTime',
        'updatedTime',
        'likeNum'
    );
    public function getNote($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function findNotesByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? ORDER BY createdTime DESC";

        return $this->getConnection()->fetchAll($sql, array($userId, $courseId));
    }

    public function addNote($noteInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $noteInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert noteInfo error.');
        }

        return $this->getNote($this->getConnection()->lastInsertId());
    }

    public function updateNote($id, $noteInfo)
    {
        $this->getConnection()->update($this->table, $noteInfo, array('id' => $id));

        return $this->getNote($id);
    }

    public function deleteNote($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function count($id, $field, $diff)
    {
        $fields = array('likeNum');
        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function getNoteByUserIdAndLessonId($userId, $lessonId)
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
        $this->filterStartLimit($start, $limit);
        $this->validateOrderBy($orderBy, $this->allowedOrderFields);
        $builder = $this->createSearchNoteQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);
        foreach ($orderBy as $field => $order) {
            $builder->addOrderBy($field, $order);
        }
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchNoteCount($conditions)
    {
        $builder = $this->createSearchNoteQueryBuilder($conditions)
            ->select('count(id)');

        return $builder->execute()->fetchColumn(0);
    }
    
    public function getNoteCountByUserIdAndCourseId($userId, $courseId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE userId = ? AND courseId = ?";

        return $this->getConnection()->fetchColumn($sql, array($userId, $courseId));
    }

    protected function createSearchNoteQueryBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'note')
            ->andWhere('userId = :userId')
            ->andWhere('courseId = :courseId')
            ->andWhere('lessonId = :lessonId')
            ->andWhere('status = :status')
            ->andWhere('content LIKE :content')
            ->andWhere('courseId IN (:courseIds)');

        return $builder;
    }
}
