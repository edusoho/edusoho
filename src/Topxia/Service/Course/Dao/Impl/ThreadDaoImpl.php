<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\ThreadDao;

class ThreadDaoImpl extends BaseDao implements ThreadDao
{

	protected $table = 'course_thread';

	public function getThread($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

    public function findThreadsByUserIdAndType($userId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND type = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($userId, $type));
    }

	public function deleteThreadsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="DELETE FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->executeUpdate($sql, $ids);
    }

	public function findThreadsByCourseId($courseId, $orderBy, $start, $limit)
	{
		$orderBy = join (' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
	}

	public function findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit)
	{
		$orderBy = join (' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND type = ? ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId, $type)) ? : array();
	}

	public function searchThreads($conditions, $orderBys, $start, $limit)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->select('*')
			->from($this->table, 'thread')
			->andWhere('courseId = :courseId')
			->andWhere('lessonId = :lessonId')
			->andWhere('userId = :userId')
			->andWhere('type = :type')
			->andWhere('isStick = :isStick')
			->andWhere('isElite = :isElite')
			->andWhere('title LIKE :keywords')
			->setFirstResult($start)
			->setMaxResults($limit);
		foreach ($orderBys as $orderBy) {
			$builder->addOrderBy($orderBy[0], $orderBy[1]);
		}

		return $builder->execute()->fetchAll() ? : array();
	}

	public function searchThreadCount($conditions)
	{
		if (isset($conditions['keywords'])) {
			$conditions['keywords'] = "%{$conditions['keywords']}%";
		}

		$builder = $this->createDynamicQueryBuilder($conditions)
			->select('count(id)')
			->from($this->table, 'thread')
			->andWhere('courseId = :courseId')
			->andWhere('lessonId = :lessonId')
			->andWhere('userId = :userId')
			->andWhere('type = :type')
			->andWhere('isStick = :isStick')
			->andWhere('isElite = :isElite')
			->andWhere('title LIKE ":keywords"');
		return $builder->execute()->fetchColumn(0);
	}

	public function addThread($fields)
	{
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course thread error.');
        }
        return $this->getThread($this->getConnection()->lastInsertId());
	}

	public function updateThread($id, $fields)
	{
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getThread($id);
	}

	public function deleteThread($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function waveThread($id, $field, $diff)
	{
		$fields = array('postNum', 'hitNum', 'followNum');
		if (!in_array($field, $fields)) {
			throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减。。", $field, implode(',', $fields)));
		}
		$sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
	}

}