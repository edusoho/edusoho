<?php

namespace Tests\Unit\AppBundle\Component\Export;

use AppBundle\Component\Export\BatchExporter;
use Biz\BaseTestCase;

class BatchExporterTest extends BaseTestCase
{
    public function testCanExport()
    {
        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);
        $exporter = new BatchExporter(self::$appKernel->getContainer());
        $exporter->findExporter(['user-learn-statistics'], []);
        $result = $exporter->canExport();

        $this->assertFalse($result);
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'countUsers',
                    'returnValue' => 3,
                ],
            ]
        );
        $exporter = new BatchExporter(self::$appKernel->getContainer());
        $exporter->findExporter(['user-learn-statistics'], []);
        $result = $exporter->getCount();

        $this->assertEquals([3], $result);
    }

    public function testExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new BatchExporter(self::$appKernel->getContainer());
        $exporter->findExporter(['user-learn-statistics'], []);
        $result = $exporter->export();

        $this->assertEquals('finish', $result['status']);
    }

    public function testExportFileWithCsv()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new BatchExporter(self::$appKernel->getContainer());
        $exporter->findExporter(['user-learn-statistics'], []);
        $result = $exporter->export();
        $result = $exporter->exportFile('user-learn-statistics', $result['csvName']);

        $this->assertNotEmpty(strpos($result[1], 'csv'));
    }

    public function testExportFileWithZip()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new BatchExporter(self::$appKernel->getContainer());
        $exporter->findExporter(['user-learn-statistics', 'user-course-statistics'], []);
        $result1 = $exporter->export();
        $result2 = $exporter->export('user-course-statistics');
        $result = $exporter->exportFile('user-learn-statistics', [$result1['csvName'], $result2['csvName']]);

        $this->assertNotEmpty(strpos($result[1], 'zip'));
    }
}
