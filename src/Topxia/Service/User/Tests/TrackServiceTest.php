<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\NotificationService;
use Topxia\Service\User\TrackService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class TrackServiceTest extends BaseTestCase
{
    public function testTrackXXX()
    {
       $this->assertNull(null);
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getTrackService()
    {
        return $this->getServiceKernel()->createService('User.TrackService');
    }

}