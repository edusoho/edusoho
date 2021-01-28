<?php

namespace Tests\Unit\AppBundle\Component\Office;

use Biz\BaseTestCase;
use AppBundle\Component\Office\CsvHelper;

class CsvHelperTest extends BaseTestCase
{
    public function testReadAndDelete()
    {
        $helper = new CsvHelper();
        $filePath = $this->mockFile();
        $result = $helper->read($filePath);

        $this->assertArrayEquals(
            array(
                array(
                    'first' => array(
                        'hello' => 'world',
                    ),
                    'second' => array(
                        'kuozhi' => 'college',
                    ),
                ),
            ),
            $result
        );
        $helper->delete($filePath);
    }

    public function testWrite()
    {
        $helper = new CsvHelper();
        $filePath = $this->mockFile();

        $result = $helper->write('tempFileFileFile', $filePath);

        $this->assertTrue(strpos($result->getContent(), '"world"') != -1);
        $this->assertTrue(strpos($result->getContent(), '"college"') != -1);
    }

    private function mockFile()
    {
        $fileFolder = self::$appKernel->getContainer()->getParameter('topxia.upload.private_directory');
        $filePath = $fileFolder.'mockedFile.csv';
        $subFilePath = $fileFolder.'mockedSubFile.csv';
        file_put_contents($filePath, serialize(array($subFilePath)));
        file_put_contents($subFilePath, serialize(array(
            'first' => array(
                'hello' => 'world',
            ),
            'second' => array(
                'kuozhi' => 'college',
            ),
        )));

        return $filePath;
    }
}
