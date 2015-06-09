<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\AnnouncementDataTag;

class AnnouncementDataTagTest extends BaseTestCase
{

    public function testGetData()
    {
        $Announcement1 = $this->getAnnouncementService()->createAnnouncement(array(
        "title"=>"Announcement1 ",
        "url"=>"http://",
        "userId"=>"1",
        "startTime" => "2015-05-10 15:40",
        "endTime" => "2015-05-11 15:35"
        ));

        $Announcement2 = $this->getAnnouncementService()->createAnnouncement(array(
        "title"=>"Announcement2 ",
        "url"=>"http://",
        "userId"=>"1",
        "startTime" =>"2015-05-10 15:40",
        "endTime" => "2015-05-11 15:35"
        ));

        $Announcement3 = $this->getAnnouncementService()->createAnnouncement(array(
        "title"=>"Announcement3 ",
        "url"=>"http://",
        "userId"=>"1",
        "startTime" =>"2015-05-10 15:40",
        "endTime" => "2015-05-11 15:35"
        ));

        $Announcement4 = $this->getAnnouncementService()->createAnnouncement(array(
        "title"=>"Announcement4 ",
        "url"=>"http://",
        "userId"=>"1",
        "startTime" =>"2015-05-10 15:40",
        "endTime" => "2015-05-11 15:35"
        ));

        $Announcement5 = $this->getAnnouncementService()->createAnnouncement(array(
        "title"=>"Announcement5 ",
        "url"=>"http://",
        "userId"=>"1",
        "startTime" =>"2015-05-10 15:40",
        "endTime" => "2015-05-11 15:35"
        ));

        $datatag = new AnnouncementDataTag();
        $Announcement = $datatag->getData(array('count' => "5"));
        $this->assertEquals(5, count($Announcement));
    }

    private function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement.AnnouncementService');
    }
}
