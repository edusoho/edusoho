<?php
namespace Topxia\Service\Announcement\AnnouncementProcessor;

use Topxia\Service\Announcement\AnnouncementProcessor\AnnouncementProcessor;

class AnnouncementProcessorFactory
{

	public static function create($target)
    {
    	if(empty($target)) {
    		throw new Exception("公告类型不存在");
    	}

    	$class = __NAMESPACE__ . '\\' . ucfirst($target). 'AnnouncementProcessor';

    	return new $class();
    }

}


