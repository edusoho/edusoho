<?php
namespace Biz\Announcement\Service;

interface AnnouncementService
{
	public function searchAnnouncements($conditions, $sort, $start, $limit);
    
    public function searchAnnouncementsCount($conditions);

    public function getAnnouncement($id);

    public function createAnnouncement($fields);

    public function updateAnnouncement($id, $fields);

    public function deleteAnnouncement($id);
}