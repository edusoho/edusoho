<?php

namespace Topxia\Service\Photo\Dao;

interface PhotoCommentDao
{
	public function getComment($id);

	public function findCommentsByFileId($fileId, $orderBy, $start, $limit);

	public function searchCommentCount($conditions);

	public function addComment($thread);

	public function updateComment($id, $fields);

	public function deleteComment($id);

	public function deleteCommentByIds(array $ids);

}