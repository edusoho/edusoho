<?php
namespace Topxia\Service\Announcement\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Announcement\AnnouncementService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;

class AnnouncementServiceImpl extends BaseService implements AnnouncementService
{
	public function searchAnnouncements($conditions, $orderBy, $start, $limit)
    {
        
        $conditions = $this->_prepareSearchConditions($conditions);
        $orders = $this->getAnnouncementDao()->searchAnnouncements($conditions, $orderBy, $start, $limit);

        return ArrayToolkit::index($orders, 'id');
    }

	public function getAnnouncement($targetId, $id)
	{
		$announcement = $this->getAnnouncementDao()->getAnnouncement($id);
		if (empty($announcement) or $announcement['targetId'] != $targetId) {
			$this->createNotFoundException("公告(#{$id})不存在。");
		}
		return $announcement;
	}

	public function createAnnouncement($targetType, $targetId, $fields)
	{
        if (!ArrayToolkit::requireds($fields, array('content'))) {
        	$this->createNotFoundException("公告数据不正确，创建失败。");
        }

        if(isset($fields['content'])){
        	$fields['content'] = $this->purifyHtml($fields['content']);
        }

		$announcement = array();
		$announcement['targetId'] = $targetId;
		$announcement['targetType'] = $targetType;
		$announcement['content'] = $fields['content'];
		$announcement['userId'] = $this->getCurrentUser()->id;
		$announcement['createdTime'] = time();

		return $this->getAnnouncementDao()->addAnnouncement($announcement);
	}



	public function updateAnnouncement($targetId, $id, $fields)
	{
        $announcement = $this->getAnnouncement($targetId, $id);
        if(empty($announcement)) {
        	$this->createNotFoundException("公告#{$id}不存在。");
        }

        if (!ArrayToolkit::requireds($fields, array('content'))) {
        	$this->createNotFoundException("公告数据不正确，更新失败。");
        }
        
        if(isset($fields['content'])){
        	$fields['content'] = $this->purifyHtml($fields['content']);
        }

        return $this->getAnnouncementDao()->updateAnnouncement($id, array(
        	'content' => $fields['content']
    	));
	}

	public function deleteAnnouncement($targetId, $id)
	{
		$announcement = $this->getAnnouncement($targetId, $id);
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
    	$targetType = array('course','classroom');
    	if(!in_array($conditions['targetType'],$targetType)){
    		throw $this->createServiceException('targetType不正确！');
    	}

    	return $conditions;
    }

    private function getCourseService(){
    	return $this->createService('Course.CourseService');
    }
}