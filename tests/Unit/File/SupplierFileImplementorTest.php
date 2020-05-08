<?php

namespace Tests\Unit\File;

use Biz\BaseTestCase;
use Biz\File\Service\Impl\SupplierFileImplementorImpl;

class SupplierFileImplementorTest extends BaseTestCase
{
    public function testGetFile()
    {
        $result = $this->getSupplierFileImplementor()->getFile([
            'convertParams' => [],
            'metas' => '{}',
            'metas2' => '{"test":"test1"}',
        ]);
        $this->assertEquals([], $result['convertParams']);
        $this->assertEquals([], $result['metas']);
        $this->assertEquals(['test' => 'test1'], $result['metas2']);
    }

    protected function mockUploadFile($file)
    {
        $this->mockBiz('S2B2C:FileSourceService', [
            [
                'functionName' => 'getFullFileInfo',
                'returnValue' => $file,
            ],
        ]);

        $this->mockBiz('File:UploadFileDao', [
            [
                'functionName' => 'getByGlobalId',
                'returnValue' => $file,
            ],
        ]);
    }

    public function testGetFullFile()
    {
        $file = ['globalId' => 1, 'hashId' => 'testHashId'];

        $this->mockUploadFile($file);

        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', [
            [
                'functionName' => 'getProductResource',
                'returnValue' => [
                    'no' => 'test no',
                    'reskey' => 'test reskey',
                    'size' => 123,
                    'name' => 'test name',
                ],
            ],
        ]);

        $result = $this->getSupplierFileImplementor()->getFullFile($file);
        $mockedS2B2C->shouldHaveReceived('getProductResource')->times(1);

        $expected = [
            'globalId' => 1,
            'hashId' => 'testHashId',
            'convertParams' => [],
            'metas2' => [],
            'storage' => 'supplier',
            'no' => 'test no',
            'reskey' => 'test reskey',
            'size' => 123,
            'name' => 'test name',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetFileByGlobalId()
    {
        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', [
            [
                'functionName' => 'getProductResource',
                'returnValue' => [
                    'no' => 'test no',
                    'reskey' => 'test reskey',
                    'size' => 123,
                    'name' => 'test name',
                ],
            ],
        ]);

        $this->mockUploadFile(['globalId' => 1, 'hashId' => 'testHashId']);

        $result = $this->getSupplierFileImplementor()->getFileByGlobalId(1);
        $mockedS2B2C->shouldHaveReceived('getProductResource')->times(1);

        $expected = [
            'globalId' => 1,
            'hashId' => 'testHashId',
            'convertParams' => [],
            'metas2' => [],
            'storage' => 'supplier',
            'no' => 'test no',
            'reskey' => 'test reskey',
            'size' => 123,
            'name' => 'test name',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPlayer()
    {
        $this->mockUploadFile(['globalId' => 1, 'hashId' => 'testHashId']);

        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', [
            [
                'functionName' => 'getProductResourcePlayer',
                'returnValue' => [
                    'testPlayer' => 'test player',
                ],
            ],
        ]);

        $result = $this->getSupplierFileImplementor()->player(1);
        $mockedS2B2C->shouldHaveReceived('getProductResourcePlayer')->times(1);

        $this->assertEquals(['testPlayer' => 'test player'], $result);
    }

    public function testGetDownloadFile()
    {
        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', [
            [
                'functionName' => 'getProductResDownload',
                'returnValue' => [
                    'testDownload' => 'test download',
                ],
            ],
        ]);

        $file = ['globalId' => 1, 'hashId' => 'testHashId'];
        $this->mockUploadFile($file);

        $result = $this->getSupplierFileImplementor()->getDownloadFile($file);
        $mockedS2B2C->shouldHaveReceived('getProductResDownload')->times(1);

        $expected = [
            'testDownload' => 'test download',
            'type' => 'url',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @return SupplierFileImplementorImpl
     */
    protected function getSupplierFileImplementor()
    {
        return $this->getBiz()->service('File:SupplierFileImplementor');
    }
}
