<?php

namespace Tests\Unit\User\Service;

use Biz\BaseTestCase;

class UpgradeNoticeServiceTest extends BaseTestCase
{
    public function testGetNotice()
    {
        $fields = array(
            'userId' => 1,
            'version' => '7.0.0',
            'code' => 'MAIN',
        );
        $notice = $this->getUpgradeNoticeService()->addNotice($fields);
        $userNotice = $this->getUpgradeNoticeService()->getNotice($notice['id']);

        $this->assertEquals($fields['version'], $userNotice['version']);
        $this->assertEquals($fields['code'], $userNotice['code']);
    }

    public function testGetNoticeByUserIdAndVersionAndCode()
    {
        $fields1 = array(
            'userId' => 1,
            'version' => '7.0.0',
            'code' => 'MAIN',
        );
        $notice1 = $this->getUpgradeNoticeService()->addNotice($fields1);

        $fields2 = array(
            'userId' => 2,
            'version' => '7.0.0',
            'code' => 'MAIN',
        );
        $notice2 = $this->getUpgradeNoticeService()->addNotice($fields2);

        $notice = $this->getUpgradeNoticeService()->getNoticeByUserIdAndVersionAndCode(1, '7.0.0', 'MAIN');

        $this->assertEquals($fields1['version'], $notice['version']);
        $this->assertEquals($fields1['code'], $notice['code']);
    }

    public function testAddNotice()
    {
        $fields = array(
            'userId' => 1,
            'version' => '7.0.0',
            'code' => 'MAIN',
        );
        $notice = $this->getUpgradeNoticeService()->addNotice($fields);

        $this->assertEquals($fields['version'], $notice['version']);
        $this->assertEquals($fields['code'], $notice['code']);
    }

    protected function getUpgradeNoticeService()
    {
        return $this->createService('User:UpgradeNoticeService');
    }
}
