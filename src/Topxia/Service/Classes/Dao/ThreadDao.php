<?php

namespace Topxia\Service\Classes\Dao;

interface ThreadDao
{
	public function getThread($id);

	public function findLatestThreadsByType($type, $start, $limit);

	public function findThreadsByUserIdAndType($userId, $type);

	public function findThreadsByClassId($classId, $orderBy, $start, $limit);

	public function findEliteThreadsByType($type, $status, $start, $limit);

	public function findThreadsByClassIdAndType($classId, $type, $orderBy, $start, $limit);

	public function searchThreads($conditions, $orderBys, $start, $limit);

	public function searchThreadCount($conditions);
	
	public function addThread($thread);

	public function deleteThread($id);

	public function updateThread($id, $fields);

	public function waveThread($id, $field, $diff);

}