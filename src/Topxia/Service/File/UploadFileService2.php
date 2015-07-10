<?php
namespace Topxia\Service\File;

interface UploadFileService2
{

	public function initUpload($params);

	public function finishedUpload($fileId);

}