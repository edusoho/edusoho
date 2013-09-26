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
		return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'activity_thread_post')
            ->where("threadId = :threadId")
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1])
            ->setParameter(":threadId", $threadId)
            ->execute()
            ->fetchAll();
	}

	public function getPostCountByThreadId($threadId)
	{
		return $this->createQueryBuilder()
            ->select('COUNT(*)')->from($this->table, 'activity_thread_post')
            ->where("threadId = :threadId")
            ->setParameter(":threadId", $threadId)
            ->execute()
            ->fetchColumn(0);
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