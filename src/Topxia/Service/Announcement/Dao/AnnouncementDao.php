<?php
namespace Topxia\Service\Announcement\Dao;

interface AnnouncementDao
{
	public function searchAnnouncements($conditions,$orderBys,$start,$limit);

	public function getAnnouncement($id);

	public function addAnnouncement($fields);

	public function deleteAnnouncement($id);

	public function updateAnnouncement($id, $fields);
}