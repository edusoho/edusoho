<?php
namespace Topxia\Service\File\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

// TODO

class UploadFileServiceTest extends BaseTestCase
{

    public function testUploadFileXXX()
    {
       $this->assertNull(null);
    }

	private function getUploadFileService()
	{
		return $this->getServiceKernel()->createService('File.UploadFileService');
	}

}
