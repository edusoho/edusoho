<?php
namespace Topxia\Service\File\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;


class FileEventSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return array(
            'upload.file.create'=> 'onUploadFileCreate',
            'upload.file.delete'=> 'onUploadFileDelete'
        );
    }

    public function onUploadFileCreate(ServiceEvent $event)
    {
    	$data= $event->getSubject();
        $file = $data['file'];
        $fileInfo =  $data['fileInfo'];
    	$courseIds = $this->getCourseService()->findCoursesByParentId($file['targetId']);
        $file['pId'] = $file['id'];
    	unset($file['id'],$file['targetId']);
    	foreach ($courseIds as $value) {
    		$file['targetId'] = $value;
    		if ($file['storage'] == 'local') {
    			$file['hashId'] = explode('/', $file['hashId']);
    			$file['hashId'][1] = $value;
    			$file['hashId'] =implode('/', $file['hashId']);
    			$file['convertHash'] = explode('/', $file['convertHash']);
    			$file['convertHash'][1] = $value;
    			$file['convertHash'] =implode('/', $file['convertHash']);
    		}

            if($file['storage'] == 'cloud'){
                $arr = explode('/', $file['hashId']);
                $file['hashId'] =explode('-',$arr[0]);
                $file['hashId'][1] = $value;
                $arr[0] = implode('-', $file['hashId']);
                $file['hashId'] = implode('/', $arr);


                if (empty($fileInfo['convertHash'])) {
                    $file['convertHash'] = "ch-{$file['hashId']}";
                } else if('document' == FileToolkit::getFileTypeByExtension($file['ext'])) {
                    $file['convertHash'] = "{$fileInfo['convertHash']}";                    
                }else{
                    $file['convertHash'] = "{$fileInfo['convertHash']}";
                }
            }
    		$this->getUploadFileService()->createFile($file);
    	}

    }

    public function onUploadFileDelete(ServiceEvent $event)
    {
        $pId = $event->getSubject();
        $this->getUploadFileService()->deleteFilesByPid($pId);
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