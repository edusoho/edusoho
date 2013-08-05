<?php

namespace Topxia\Service\Content\Dao;

interface CommentDao
{

	public function getComment($id);

	public function addComment($comment);

	public function deleteComment($id);

	public function findCommentsByObjectTypeAndObjectId($objectType, $objectId, $start, $limit);

	public function findCommentsByObjectType($objectType, $start, $limit);

	public function findCommentsCountByObjectType($objectType);
}