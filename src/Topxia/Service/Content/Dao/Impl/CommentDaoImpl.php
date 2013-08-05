<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\CommentDao;

class CommentDaoImpl extends BaseDao implements CommentDao
{
    protected $table = 'comment';

	public function getComment($id)
	{
		return $this->fetch($id);
	}

	public function addComment($comment)
	{
		$id = $this->insert($comment);
       	return $this->getComment($id);
	}

	public function deleteComment($id)
	{
		return $this->delete($id);
	}

	public function findCommentsByObjectTypeAndObjectId($objectType, $objectId, $start, $limit)
	{
		$sql = "SELECT * FROM {$this->table} WHERE objectType = ? AND objectId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($objectType, $objectId));
	}

	public function findCommentsByObjectType($objectType, $start, $limit)
	{
		$sql = "SELECT * FROM {$this->table} WHERE objectType = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($objectType));
	}

	public function findCommentsCountByObjectType($objectType)
	{
		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE  objectType = ?";
        return $this->getConnection()->fetchColumn($sql, array($objectType));
	}

}