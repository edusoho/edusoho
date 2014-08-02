<?php

namespace Topxia\Service\Course\Dao;

interface LessonViewDao
{
	public function getLessonView($id);

	public function addLessonView($lessonView);

	public function searchLessonViewCount($conditions);

	public function getAnalysisLessonMinTime($type);

	public function searchLessonView($conditions, $orderBy, $start, $limit);

	public function searchLessonViewGroupByTime($startTime,$endTime,$conditions);
}