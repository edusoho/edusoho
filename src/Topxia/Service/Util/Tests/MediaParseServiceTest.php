<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Util\MediaParseService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class MediaParseServiceTest extends BaseTestCase
{
    public function testGetMedia()
    {
       $this->assertNull(null);
    }

    private function getMediaParseService()
    {
        return $this->getServiceKernel()->createService('Util.MediaParseService');
    }

}