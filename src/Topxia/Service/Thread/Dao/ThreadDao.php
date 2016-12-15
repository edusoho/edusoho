<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadDao
{
	public function getThread($id);

	public function findThreadsByTargetAndUserId($target, $userId, $start, $limit);

	public function findThreadsByTargetAndPostNum($target, $postNum, $start, $limit);

	public function addThread($thread);

	public function deleteThread($id);

	public function updateThread($id, $fields);

	public function waveThread($id, $field, $diff);

}