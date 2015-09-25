<?php
namespace Topxia\Service\Util\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Util\SystemUtilService;

class SystemUtilServiceImpl extends BaseService implements SystemUtilService
{


    //TODO 删除之前检查该文件是否被其他课程使用
	public function removeUnusedUploadFiles()
	{
		$targets = $this->getSystemUtilDao()->getCourseIdsWhereCourseHasDeleted();
		if(empty($targets)){
			return array("success"=>false,'message'=>'无可优化文件');
		}
		$targets = $this->plainTargetId($targets);
		foreach ($targets as $target) {
	        $conditions = array(
	            'targetType'=> 'courselesson', 
	            'targetId'=>$target
	        );
        	$uploadFiles = $this->getUploadFileService()->searchFiles(
	            $conditions,
	            'latestCreated',
	            0,
	            1000
	        );
			$this->removeUploadFiles($uploadFiles);
		}
		return array("success"=>true,'message'=>'优化文件');
	}

	protected function plainTargetId($targets)
	{
		$result = array();
		foreach ($targets as $target) {
			$result[] = $target['targetId'];
		}
		return $result;
	}

	protected function removeUploadFiles($uploadFiles)
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