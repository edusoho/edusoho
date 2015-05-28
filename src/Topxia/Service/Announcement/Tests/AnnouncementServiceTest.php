<?php

namespace Topxia\Service\Announcement\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;

class AnnouncementServiceTest extends BaseTestCase
{
    public function testCreateAnnouncement()
    {   
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin@adm1in.com',
            'password'=>'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_ADMIN')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $testAnnouncement = array(
            'title' =>'test',
            'url' =>'http://test.com',
            'startTime' => '2015-4-5',
            'endTime' => '2015-10-5'
            );

        $announcement = $this->getAnnouncementService()->createAnnouncement($testAnnouncement);

        $this->assertEquals($announcement['title'],$testAnnouncement['title']);
        $this->assertEquals($announcement['url'],$testAnnouncement['url']);
        $this->assertEquals(strtotime($testAnnouncement['startTime']),$announcement['startTime']);
        $this->assertEquals(strtotime($testAnnouncement['endTime']),$announcement['endTime']);
        $this->assertEquals($announcement['userId'],3);

    }

    public function testDeleteAnnouncement()
    {   
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin@adm1in.com',
            'password'=>'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_ADMIN')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $testAnnouncement = array(
            'title' =>'test',
            'url' =>'http://test.com',
            'startTime' => '2015-4-5',
            'endTime' => '2015-10-5'
            );

        $announcement = $this->getAnnouncementService()->createAnnouncement($testAnnouncement);

        $this->getAnnouncementService()->deleteAnnouncement($announcement['id']);

        $announcement=$this->getAnnouncementService()->getAnnouncement($announcement['id']);

        $this->assertEquals(null,$announcement);

    }

    public function testSearchAnnouncements()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin@adm1in.com',
            'password'=>'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_ADMIN')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $testAnnouncement = array(
            'title' =>'test',
            'url' =>'http://test.com',
            'startTime' => '2015-4-5',
            'endTime' => '2015-10-5'
            );

        $announcement = $this->getAnnouncementService()->createAnnouncement($testAnnouncement);

        $announcements=$this->getAnnouncementService()->searchAnnouncements(array(),array('createdTime','desc'),0,1);
        $this->assertEquals(3,$announcements[0]['userId']);
        $this->assertEquals('test',$announcements[0]['title']);

    }

    private function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }
}