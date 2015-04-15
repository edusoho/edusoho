<?php
namespace Topxia\Service\Announcement;

interface AnnouncementService
{
	public function searchAnnouncements($conditions, $sort, $start, $limit);
    
	public function getAnnouncement($targetId, $id);

	public function createAnnouncement($targetType, $targetId, $fields);

	public function updateAnnouncement($targetId, $id, $fields);

	public function deleteAnnouncement($targetId, $id);
}