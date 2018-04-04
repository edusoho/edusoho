<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use AppBundle\Common\ExportHelp;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\TimeMachine;
use AppBundle\Common\RandMachine;
use Topxia\Service\Common\ServiceKernel;

class ExportHelpTest extends BaseTestCase
{
    public function testGetMagicExportSetting()
    {
        $request = new Request(
            array('start' => 100)
        );
        $result = ExportHelp::getMagicExportSetting($request);
        $this->assertArrayEquals(array(100, 1000, 10000), $result);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'export_limit' => 1001,
                    'export_allow_count' => 10001,
                ),
            ),
        ));
        $result = ExportHelp::getMagicExportSetting($request);
        $this->assertArrayEquals(array(100, 1001, 10001), $result);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'export_limit' => 10001,
                    'export_allow_count' => 1001,
                ),
            ),
        ));
        $result = ExportHelp::getMagicExportSetting($request);
        $this->assertArrayEquals(array(100, 1001, 1001), $result);
    }

    public function testAddFileTitle()
    {
        $request = new Request(
            array('fileName' => 'ExportHelpTest')
        );
        $result = ExportHelp::addFileTitle($request, 'test', 'content');
        $this->assertEquals('ExportHelpTest', $result);
    }

    public function testGetNextMethod()
    {
        $result = ExportHelp::getNextMethod(1, 2);
        $this->assertEquals('getData', $result);

        $result = ExportHelp::getNextMethod(2, 1);
        $this->assertEquals('export', $result);
    }

    public function testSaveToTempFile()
    {
        $request = new Request(
            array('fileName' => 'ExportHelpTest')
        );
        $result = ExportHelp::saveToTempFile($request, 'content', 'test');
        $this->assertEquals('test', $result);
        $result = ExportHelp::saveToTempFile($request, 'content', '');
        $this->assertEquals('ExportHelpTest', $result);
    }

    public function testGenereateExportCsvFileName()
    {
        TimeMachine::setMockedTime(time());
        RandMachine::setMockedRand(rand());
        $result = ExportHelp::genereateExportCsvFileName('test');
        $biz = $this->getBiz();
        $user = $biz['user'];
        $fileName = md5('test'.$user->getId().TimeMachine::time()).RandMachine::rand();
        $this->assertEquals($fileName, $result);
    }

    public function testGetFilePath()
    {
        $fileName = 'test';
        $result = ExportHelp::getFilePath('test');
        $this->assertEquals(ServiceKernel::instance()->getParameter('topxia.upload.private_directory').'/'.basename($fileName), $result);
    }
}
