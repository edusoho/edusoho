<?php
namespace Topxia\Service\Course\Dao;

interface CourseAnnouncementDao
{
	public function getAnnouncement($id);

	public function findAnnouncementsByCourseId($courseId, $start, $limit);

	public function findAnnouncementsByCourseIds($ids, $start, $limit);

	public function addAnnouncement($fields);

	public function deleteAnnouncement($id);

	public function updateAnnouncement($id, $fields);
}