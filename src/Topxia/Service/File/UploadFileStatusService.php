<?php
namespace Topxia\Service\File;

interface UploadFileStatusService
{   

	public function setUploadFileStatus(array $fields);

    public function getUploadFileStatusByKey($key);

    public function deleteUploadFileStatus($key);

}