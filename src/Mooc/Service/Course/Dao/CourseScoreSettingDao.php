<?php
namespace Mooc\Service\Course\Dao;

interface CourseScoreSettingDao {
	
	public function getScoreSettingByCourseId($courseId);

	public function findScoreSettingsByCourseIds($courseIds);

	public function addScoreSetting($scoreSetting);
	
	public function updateScoreSetting($courseId, $fields);
}
