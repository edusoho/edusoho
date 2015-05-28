<?php
namespace Topxia\Service\Announcement\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;


class AnnouncementServiceImpl extends BaseService implements AnnouncementService
{
    public function getAnnouncement($id)
    {
        return $this->getAnnouncementDao()->getAnnouncement($id);
    }

    public function searchAnnouncements($conditions, $orderBy, $start, $limit)
    {
        return $this->getAnnouncementDao()->searchAnnouncements($conditions, $orderBy, $start, $limit);
    }

    public function searchAnnouncementsCount($conditions)
    {
        return $this->getAnnouncementDao()->searchAnnouncementsCount($conditions);
    }

    public function deleteAnnouncement($id)
    {
        return $this->getAnnouncementDao()->deleteAnnouncement($id);
    }

    public function createAnnouncement($announcement)
    {
        if (!isset($announcement['title']) || empty($announcement['title'])) {
            throw $this->createServiceException("公告内容不能为空！");
        }

        if (!isset($announcement['startTime']) || empty($announcement['startTime'])) {
            throw $this->createServiceException("发布时间不能为空！");
        }

        if (!isset($announcement['endTime']) || empty($announcement['endTime'])) {
            throw $this->createServiceException("结束时间不能为空！");
        }

        $user =$this->getCurrentUser();

        $announcement['title'] = trim($announcement['title']);
        $announcement['userId'] = $user->id;
        $announcement['createdTime'] = time();
        $announcement['startTime'] = strtotime($announcement['startTime']);
        $announcement['endTime'] = strtotime($announcement['endTime']);

        $announcement = $this->getAnnouncementDao()->createAnnouncement($announcement);

        return $announcement;
    }

    public function updateAnnouncement($id, $announcement)
    {   
        if (!isset($announcement['title']) || empty($announcement['title'])) {
            throw $this->createServiceException("公告内容不能为空！");
        }

        if (!isset($announcement['startTime']) || empty($announcement['startTime'])) {
            throw $this->createServiceException("发布时间不能为空！");
        }

        if (!isset($announcement['endTime']) || empty($announcement['endTime'])) {
            throw $this->createServiceException("结束时间不能为空！");
        }

        $user =$this->getCurrentUser();

        $announcement['title'] = trim($announcement['title']);
        $announcement['userId'] = $user->id;
        $announcement['startTime'] = strtotime($announcement['startTime']);
        $announcement['endTime'] = strtotime($announcement['endTime']);

        return $this->getAnnouncementDao()->updateAnnouncement($id, $announcement);
    }

    private function getAnnouncementDao() 
    {
        return $this->createDao('Announcement.AnnouncementDao');
    }
}