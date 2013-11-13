<?php
namespace Topxia\Service\Util\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Util\SystemUtilService;

class SystemUtilServiceImpl extends BaseService implements SystemUtilService
{

	public function removeUnusedUploadFiles()
	{
		$targets = $this->getSystemUtilDao()->getCourseIdsWhereCourseHasDeleted();
		if(empty($targets)) return ;
		foreach ($targets as $target) {
	        $conditions = array(
	            'targetType'=> 'courselesson', 
	            'targetId'=>$target['targetId']
	        );
        	$uploadFiles = $this->getUploadFileService()->searchFiles(
	            $conditions,
	            'latestCreated',
	            0,
	            1000
	        );
			$this->removeUploadFiles($uploadFiles);
		}
	}

	private function removeUploadFiles($uploadFiles)
	{
		foreach ($uploadFiles as $file) {
			$this->getUploadFileService()->deleteFile($file['id']);
		}
	}
	

    protected function getSystemUtilDao ()
    {
        return $this->createDao('Util.SystemUtilDao');
    }    


  	protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

}