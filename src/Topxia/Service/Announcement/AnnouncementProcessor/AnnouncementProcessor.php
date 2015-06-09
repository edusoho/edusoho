<?php
namespace Topxia\Service\Announcement\AnnouncementProcessor;

interface AnnouncementProcessor 
{
    public function checkManage($targetId);

    public function checkTake($targetId);

    public function getTargetShowUrl();

	public function announcementNotification($targetId, $targetObject, $targetObjectShowUrl);

	public function tryManageObject($targetId);
	
	public function getTargetObject($targetId);

	public function getShowPageName($targetId);

}