<?php

namespace Tests\Unit\Component\Export;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\Exporter;
use Symfony\Component\Filesystem\Filesystem;

class ExporterTest extends BaseTestCase
{
    public function testBuildParameter()
    {
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), array());
        $result = $expoter->buildParameter(array());

        $this->assertEquals(0, $result['start']);
        $this->assertEquals('', $result['fileName']);

        $result = $expoter->buildParameter(array('start' => 1, 'fileName' => './../test.csv'));
        $this->assertEquals(1, $result['start']);
        $this->assertEquals('test.csv', $result['fileName']);
    }

    public function testExportFileRootPath()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), array());
        $path = ReflectionUtils::invokeMethod($expoter, 'exportFileRootPath');

        $this->assertTrue($filesystem->exists($path));
        $this->assertEquals($biz['topxia.upload.private_directory'].'/', $path);
    }

    public function testTransTitles()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), array());
        $titles = ReflectionUtils::invokeMethod($expoter, 'transTitles');

        $this->assertArrayEquals(array('标题'), $titles);
    }

    public function testGetPageConditions()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), array());
        list($start, $limit) = ReflectionUtils::invokeMethod($expoter, 'getPageConditions');
        $this->assertEquals(1000, $limit);
        $this->getSettingService()->set('magic', array('export_limit' => 10000));
        // $this->mockBiz(
        //     'System:SettingService',
        //     array(
        //         array(
        //         'functionName' => 'get',
        //         'returnValue' => array('export_limit' => 10000),
        //         ),
        //     )
        // );
        // list($start, $limit) = ReflectionUtils::invokeMethod($expoter, 'getPageConditions');
        // $this->assertEquals(10000, $limit);
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}

class ExpoertWrap extends Exporter
{
    public function getTitles()
    {
        return array('标题');
    }

    public function getContent($start, $limit)
    {
    }

    public function canExport()
    {
    }

    public function getCount()
    {
    }

    public function buildCondition($conditions)
    {
    }
}
