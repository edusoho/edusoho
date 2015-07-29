<?php
namespace Topxia\Service\File\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;


class FileEventSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return array(
            'upload.file.create'=> 'onUploadFileCreate'
        );
    }

    public function onUploadFileCreate(ServiceEvent $event)
    {
    	$file = $event->getSubject();
    	$courseIds = $this->getCourseService()->findCoursesByParentId($file['targetId']);
    	$pId = $file['id'];
    	unset($file['id'],$file['targetId'],$file['pId']);
    	foreach ($courseIds as $value) {
    		$file['targetId'] = $value;
    		if ($file['storage'] == 'local') {
    			$file['hashId'] = explode('/', $file['hashId']);
    			$file['hashId'][1] = $value;
    			$filxe['hashId'] =implode('/', $file['hashId']);
    			$file['convertHash'] = explode('/', $file['convertHash']);
    			$file['convertHash'][1] = $value;
    			$file['convertHash'] =implode('/', $file['convertHash']);
    		}
    		$this->getUploadFileService()->createFile($file);
    	}

    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return ServiceKernel::instance()->createService('File.UploadFileService');
    }
}