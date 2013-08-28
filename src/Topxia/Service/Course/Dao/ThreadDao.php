<?php

namespace Topxia\Service\Course\Dao;

interface ThreadDao
{
	public function getThread($id);

	public function findThreadsByUserIdAndType($userId, $type);

	public function findThreadsByCourseId($courseId, $orderBy, $start, $limit);

	public function findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit);

	public function searchThreads($conditions, $orderBys, $start, $limit);

	public function searchThreadCount($conditions);

	public function addThread($thread);

	public function deleteThread($id);

	public function updateThread($id, $fields);

	public function waveThread($id, $field, $diff);

}