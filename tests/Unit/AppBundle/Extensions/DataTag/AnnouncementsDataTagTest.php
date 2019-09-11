<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\AnnouncementsDataTag;
use Biz\Announcement\Service\AnnouncementService;
use Biz\BaseTestCase;
use Biz\User\CurrentUser;

class AnnouncementsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->getAnnouncementService()->createAnnouncement(array(
            'content' => 'Announcement1',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 2,
            'targetType' => 'course',
            'targetId' => '1',
        ));

        $this->getAnnouncementService()->createAnnouncement(array(
            'content' => 'Announcement2',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 2,
            'targetType' => 'course',
            'targetId' => '1',
        ));

        $this->getAnnouncementService()->createAnnouncement(array(
            'content' => 'Announcement3',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 2,
            'targetType' => 'classroom',
            'targetId' => '1',
        ));

        $this->getAnnouncementService()->createAnnouncement(array(
            'content' => 'Announcement4',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 2,
            'targetType' => 'classroom',
            'targetId' => '1',
        ));

        $this->getAnnouncementService()->createAnnouncement(array(
            'content' => 'Announcement5',
            'url' => 'http://',
            'userId' => '1',
            'startTime' => time() + 3600,
            'endTime' => time() + 3600 * 2,
            'targetType' => 'global',
            'targetId' => '1',
        ));

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1, 'orgCode' => '1.'),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $dataTag = new AnnouncementsDataTag();
        $announcement = $dataTag->getData(array('count' => '5', 'targetType' => 'course', 'targetId' => 1));
        $this->assertEquals(2, count($announcement));

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1, 'orgCode' => '1.2.'),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $announcement = $dataTag->getData(array('count' => '5', 'targetType' => 'course', 'targetId' => 1));
        $this->assertEquals(0, count($announcement));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage count参数缺失
     */
    public function testEmptyCount()
    {
        $dataTag = new AnnouncementsDataTag();
        $announcement = $dataTag->getData(array('targetType' => 'course', 'targetId' => 1));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage count参数超出最大取值范围
     */
    public function testCountGT100()
    {
        $dataTag = new AnnouncementsDataTag();
        $announcement = $dataTag->getData(array('count' => 101, 'targetType' => 'course', 'targetId' => 1));
    }

    /**
     * @return AnnouncementService
     */
    private function getAnnouncementService()
    {
        return $this->createService('Announcement:AnnouncementService');
    }
}
