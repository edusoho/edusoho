<?php

namespace Topxia\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Activity\Dao\ThreadPostDao;

class ThreadPostDaoImpl extends BaseDao implements ThreadPostDao
{

	protected $table = 'activity_thread_post';

	public function getPost($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
		
	}

	public function findPostsByThreadId($threadId, $orderBy, $start, $limit)
	{
        $orderBy = join (' ', $orderBy);
        $sql = "SELECT * FROM {$this->table} WHERE threadId = ? ORDER BY {$orderBy} LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($threadId)) ? : array();
	}

	public function getPostCountByThreadId($threadId)
	{
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE threadId = ?";
        return $this->getConnection()->fetchColumn($sql, array($threadId));
	}

	public function addPost(array $post)
	{

		$affected = $this->getConnection()->insert($this->table, $post);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert ActivityThreadPost error.');
        }
        return $this->getPost($this->getConnection()->lastInsertId());
	}

	public function deletePost($id)
	{
		return $this->getConnection()->delete(self::TABLENAME, array('id' => $id));
	}

	public function deletePostsByThreadId($threadId)
	{	
		 $sql ="DELETE FROM {$this->table} WHERE threadId = ?";
         return $this->getConnection()->executeUpdate($sql, array($threadId));
	}

}