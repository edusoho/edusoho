<?php

namespace Topxia\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Activity\Dao\ThreadPostDao;

class ThreadPostDaoImpl extends BaseDao implements ThreadPostDao
{

	protected $table = 'activity_thread_post';

	public function getPost($id)
	{
		return $this->fetch($id);
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
		$id = $this->insert($post);
		return $this->getPost($id);
	}

	public function deletePost($id)
	{
		return $this->delete($id);
	}

	public function deletePostsByThreadId($threadId)
	{	
		return $this->createQueryBuilder()
            ->delete($this->table)
            ->where("threadId = :threadId")
            ->setParameter(":threadId", $threadId)
            ->execute();
	}

}