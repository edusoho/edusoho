<?php

namespace Topxia\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Activity\Dao\ThreadDao;

class ThreadDaoImpl extends BaseDao implements ThreadDao
{

	protected $table = 'activity_thread';

    public function findThreadsByUserIdAndType($userId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND type = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($userId, $type));
    }

	public function getThread($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
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

	public function findThreadsByActivityId($courseId, $orderBy, $start, $limit)
	{

        $orderBy = join (' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE activityId = ? ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
	}

	public function findThreadsByActivityIdAndType($courseId, $type, $orderBy, $start, $limit)
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
			->from($this->table, 'activity_thread')
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
			->from($this->table, 'activity_thread')
			->andWhere('activityId = :activityId')
			->andWhere('lessonId = :lessonId')
			->andWhere('userId = :userId')
			->andWhere('isStick = :isStick')
			->andWhere('isElite = :isElite')
			->andWhere('title LIKE ":keywords"');
		return $builder->execute()->fetchColumn(0);
	}

	public function addThread($thread)
	{
		
		$affected = $this->getConnection()->insert($this->table, $thread);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert ActivityThread error.');
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
		$sql = "UPDATE {$this->table} SET {$field} = {$field} + ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff));
	}

}