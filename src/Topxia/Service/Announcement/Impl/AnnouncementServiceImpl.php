<?php
namespace Topxia\Service\Announcement\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;

class AnnouncementServiceImpl extends BaseService implements AnnouncementService
{

    public function getAnnouncement($id)
    {

    }


	public function searchAnnouncements($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        $announcements = $this->getAnnouncementDao()->searchAnnouncements($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($announcements, 'id');
    }

    public function searchAnnouncementsCount($conditions)
    {
        return $this->getAnnouncementDao()->searchAnnouncementsCount($conditions);
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

		$fields['userId'] = $this->getCurrentUser()->id;
		$fields['createdTime'] = time();

		return $this->getAnnouncementDao()->addAnnouncement($fields);
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

        return $this->getAnnouncementDao()->updateAnnouncement($id, array(
        	'content' => $fields['content']
    	));
	}

	public function deleteAnnouncement($id)
	{
		$announcement = $this->getAnnouncement($id);
		if(empty($announcement)) {
			$this->createNotFoundException("公告#{$id}不存在。");
		}

		$this->getAnnouncementDao()->deleteAnnouncement($id);
	}

	private function getAnnouncementDao()
    {
        return $this->createDao('Announcement.AnnouncementDao');
    }

    private function _prepareSearchConditions($conditions)
    {
    	$targetType = array('course','classroom','global');
    	if(!in_array($conditions['targetType'], $targetType)){
    		throw $this->createServiceException('targetType不正确！');
    	}

    	return $conditions;
    }

    private function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }
}