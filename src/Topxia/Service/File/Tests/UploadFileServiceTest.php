<?php
namespace Topxia\Service\File\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

// TODO

class UploadFileServiceTest extends BaseTestCase
{

	private function getUploadFileService()
	{
		return $this->getServiceKernel()->createService('File.UploadFileService');
	}

}
