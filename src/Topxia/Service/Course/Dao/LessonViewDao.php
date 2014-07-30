<?php

namespace Topxia\Service\Course\Dao;

interface LessonViewDao
{
	public function getLessonView($id);

	public function addLessonView($data);

	public function searchLessonViewCount($conditions);

	public function searchLessonViewGroupByTime($startTime,$endTime,$conditions);
}