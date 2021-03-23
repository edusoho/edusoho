<?php

namespace Tests\Unit\AppBundle\Component\Export;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\Exporter;
use Biz\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ExporterTest extends BaseTestCase
{
    public function testExport()
    {
        $filesystem = new Filesystem();
        $biz = $this->getBiz();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        $result = $expoter->export();
        $this->assertEquals(0, $result['success']);
        $this->assertEquals('export.not_allowed', $result['message']);

        $expoter->setCanExport(true);
        $result = $expoter->export();
        $this->assertEquals('finish', $result['status']);
        $this->assertEquals('1000', $result['start']);
        $this->assertEquals('1', $result['success']);
    }

    public function testAddContent()
    {
        $filesystem = new Filesystem();
        $biz = $this->getBiz();
        $filePath = $biz['topxia.upload.private_directory'].'/testcsv';
        $filesystem->remove($filePath);
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        ReflectionUtils::invokeMethod($expoter, 'addContent', [['test' => '123'], 0, $filePath]);

        $result = file_get_contents($filePath.'0');
        $result = unserialize($result);
        $this->assertEquals('标题', $result[0][0]);
        $this->assertEquals('123', $result['test']);
    }

    public function testUpdateFilePaths()
    {
        $filesystem = new Filesystem();
        $biz = $this->getBiz();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        $filePath = $biz['topxia.upload.private_directory'].'/testcsv';
        $filesystem->remove($filePath);
        $path = ReflectionUtils::invokeMethod($expoter, 'updateFilePaths', [$filePath, 1]);
        $this->assertEquals($biz['topxia.upload.private_directory'].'/testcsv1', $path);
        $result = file_get_contents($filePath);
        $result = unserialize($result);
        $this->assertEquals($biz['topxia.upload.private_directory'].'/testcsv1', $result[0]);
    }

    public function testBuildParameter()
    {
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        $result = $expoter->buildParameter([]);

        $this->assertEquals(0, $result['start']);
        $this->assertEquals('', $result['fileName']);

        $result = $expoter->buildParameter(['start' => 1, 'fileName' => './../test.csv']);
        $this->assertEquals(1, $result['start']);
        $this->assertEquals('test.csv', $result['fileName']);
    }

    public function testExportFileRootPath()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        $path = ReflectionUtils::invokeMethod($expoter, 'exportFileRootPath');

        $this->assertTrue($filesystem->exists($path));
        $this->assertEquals($biz['topxia.upload.private_directory'].'/tmp/', $path);
    }

    public function testTransTitles()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        $titles = ReflectionUtils::invokeMethod($expoter, 'transTitles');

        $this->assertArrayEquals(['标题'], $titles);
    }

    public function testGetPageConditions()
    {
        $biz = $this->getBiz();
        $filesystem = new Filesystem();
        $expoter = new ExpoertWrap(self::$appKernel->getContainer(), []);
        list($start, $limit) = ReflectionUtils::invokeMethod($expoter, 'getPageConditions');
        $this->assertEquals(1000, $limit);
        $this->getSettingService()->set('magic', ['export_limit' => 10000]);
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
        return ['标题'];
    }

    public function getContent($start, $limit)
    {
        return ['test' => 'test1'];
    }

    public function canExport()
    {
        if (empty($this->canExport)) {
            return false;
        }

        return $this->canExport;
    }

    public function getCount()
    {
    }

    public function buildCondition($conditions)
    {
    }

    public function setCanExport($value)
    {
        $this->canExport = $value;
    }
}
