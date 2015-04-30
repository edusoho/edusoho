<?php
namespace Topxia\Service\Announcement\Dao;

interface AnnouncementDao
{
    public function getAnnouncement($id);

    public function createAnnouncement($announcement);
    
    public function searchAnnouncements($conditions, $orderBy, $start, $limit);

    public function searchAnnouncementsCount($conditions);

    public function deleteAnnouncement($id);

    public function updateAnnouncement($id, $fields);
}