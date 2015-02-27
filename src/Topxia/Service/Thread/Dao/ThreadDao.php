<?php

namespace Topxia\Service\Thread\Dao;

interface ThreadDao
{
	public function getThread($id);

	public function findLatestThreadsByType($type, $start, $limit);

	public function findThreadsByUserIdAndType($userId, $type);

	public function findThreadsByCourseId($courseId, $orderBy, $start, $limit);

	public function findEliteThreadsByType($type, $status, $start, $limit);

	public function findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit);

	public function findThreadsByTargetAndUserId($target, $userId, $start, $limit);

	public function findThreadsByTargetAndPostNum($target, $postNum, $start, $limit);

	public function searchThreads($conditions, $orderBys, $start, $limit);

	public function searchThreadCount($conditions);

	public function searchThreadCountInCourseIds($conditions);

	public function searchThreadInCourseIds($conditions, $orderBys, $start, $limit);
	
	public function addThread($thread);

	public function deleteThread($id);

	public function updateThread($id, $fields);

	public function waveThread($id, $field, $diff);

}