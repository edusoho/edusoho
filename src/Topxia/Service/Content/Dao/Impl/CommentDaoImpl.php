<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\CommentDao;

class CommentDaoImpl extends BaseDao implements CommentDao
{
    protected $table = 'comment';

	public function getComment($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function addComment($comment)
	{
        $affected = $this->getConnection()->insert($this->table, $comment);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert comment error.');
        }
        return $this->getComment($this->getConnection()->lastInsertId());
	}

	public function deleteComment($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function findCommentsByObjectTypeAndObjectId($objectType, $objectId, $start, $limit)
	{
        $this->filterStartLimit($start, $limit);
		$sql = "SELECT * FROM {$this->table} WHERE objectType = ? AND objectId = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($objectType, $objectId));
	}

	public function findCommentsByObjectType($objectType, $start, $limit)
	{
        $this->filterStartLimit($start, $limit);
		$sql = "SELECT * FROM {$this->table} WHERE objectType = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($objectType));
	}

	public function findCommentsCountByObjectType($objectType)
	{
		$sql = "SELECT COUNT(*) FROM {$this->table} WHERE  objectType = ?";
        return $this->getConnection()->fetchColumn($sql, array($objectType));
	}

}