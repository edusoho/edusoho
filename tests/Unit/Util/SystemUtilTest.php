<?php

namespace Tests\Unit\Util;

use Biz\BaseTestCase;
use Biz\Util\SystemUtil;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\RandMachine;

class SystemUtilTest extends BaseTestCase
{
    public function testGetDownloadPath()
    {
        $isContains = false !== strpos(SystemUtil::getDownloadPath(), '/app/data/upgrade');
        $this->assertTrue($isContains);
    }

    public function testGetBackUpPath()
    {
        $isContains = false !== strpos(SystemUtil::getBackUpPath(), '/app/data/backup');
        $this->assertTrue($isContains);
    }

    public function testGetCachePath()
    {
        $isContains = false !== strpos(SystemUtil::getCachePath(), '/app/cache');
        $this->assertTrue($isContains);
    }

    public function testGetUploadTmpPath()
    {
        $isContains = false !== strpos(SystemUtil::getUploadTmpPath(), '/web/files/tmp');
        $this->assertTrue($isContains);
    }

    public function testBackupdb()
    {
        $text = SystemUtil::getSystemRootPath().'/web/files/tmp/mocked-rand.txt';
        $mockedDump = $this->mockBiz(
            'Mocked:MockedDump',
            array(
                array(
                    'functionName' => 'export',
                    'withParams' => array($text),
                    'returnValue' => '123',
                ),
            )
        );
        RandMachine::setMockedRand('mocked-rand');
        ReflectionUtils::setStaticProperty(new SystemUtil(), 'mockedDump', $mockedDump);

        $result = SystemUtil::backupdb();
        $mockedDump->shouldHaveReceived('export')->times(1);
        $this->assertEquals('123', $result);
    }
}
