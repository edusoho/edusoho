<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\AnnouncementDataTag;
use Biz\Announcement\Service\AnnouncementService;
use Biz\BaseTestCase;

class AnnouncementDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCount()
    {
        $dataTag = new AnnouncementDataTag();
        $dataTag->getData([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMaxCount()
    {
        $dataTag = new AnnouncementDataTag();
        $dataTag->getData(['count' => 101]);
    }

    public function testGetData()
    {
        $this->getAnnouncementService()->createAnnouncement([
            'content' => 'Announcement1',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() - 10,
            'endTime' => time() + 10,
            'targetType' => 'global',
            'targetId' => '1',
        ]);

        $this->getAnnouncementService()->createAnnouncement([
            'content' => 'Announcement3',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() - 10,
            'targetType' => 'global',
            'endTime' => time() + 10,
            'targetId' => '1',
        ]);

        $this->getAnnouncementService()->createAnnouncement([
            'content' => 'Announcement3',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() - 10,
            'endTime' => time() + 10,
            'targetType' => 'global',
            'targetId' => '1',
        ]);

        $this->getAnnouncementService()->createAnnouncement([
            'content' => 'Announcement4',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() - 10,
            'endTime' => time() + 10,
            'targetType' => 'global',
            'targetId' => '1',
        ]);

        $this->getAnnouncementService()->createAnnouncement([
            'content' => 'Announcement5',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() - 10,
            'endTime' => time() + 10,
            'targetType' => 'global',
            'targetId' => '1',
        ]);

        $dataTag = new AnnouncementDataTag();
        $announcement = $dataTag->getData(['count' => '5']);
        $this->assertEquals(5, count($announcement));
    }

    /**
     * @return AnnouncementService
     */
    private function getAnnouncementService()
    {
        return $this->getServiceKernel()->createService('Announcement:AnnouncementService');
    }
}
