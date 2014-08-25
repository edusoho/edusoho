<?php

namespace Topxia\Service\File\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileStatusService;
    
class UploadFileStatusServiceImpl extends BaseService implements UploadFileStatusService
{
	public function setUploadFileStatus(array $fields)
	{
		$uploadFileStatus = $this->getUploadFileStatusDao()->getUploadFileStatusByKey($fields['scopKey']);
		if(empty($uploadFileStatus)){
			return $this->getUploadFileStatusDao()->addUploadFileStatus($fields);
		} else {
			return $this->getUploadFileStatusDao()->updateUploadFileStatus($fields['scopKey'], $fields);
		}
	}

    public function getUploadFileStatusByKey($key)
    {
    	return $this->getUploadFileStatusDao()->getUploadFileStatusByKey($key);
    }

    public function deleteUploadFileStatus($key)
    {
    	$this->getUploadFileStatusDao()->deleteUploadFileStatus($key);
    }

    private function getUploadFileStatusDao()
    {
        return $this->createDao('File.UploadFileStatusDao');
    }
}