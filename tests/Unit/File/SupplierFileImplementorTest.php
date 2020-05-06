<?php

namespace Tests\Unit\File;

use Biz\BaseTestCase;
use Biz\File\Service\Impl\SupplierFileImplementorImpl;

class SupplierFileImplementorTest extends BaseTestCase
{
    public function testGetFile()
    {
        $result = $this->getSupplierFileImplementor()->getFile(array(
            'convertParams' => array(),
            'metas' => '{}',
            'metas2' => '{"test":"test1"}',
        ));
        $this->assertEquals(array(), $result['convertParams']);
        $this->assertEquals(array(), $result['metas']);
        $this->assertEquals(array('test' => 'test1'), $result['metas2']);
    }

    protected function mockUploadFile($file)
    {
        $this->mockBiz('S2B2C:FileSourceService', array(
            array(
                'functionName' => 'getFullFileInfo',
                'returnValue' => $file,
            ),
        ));

        $this->mockBiz('File:UploadFileDao', array(
            array(
                'functionName' => 'getByGlobalId',
                'returnValue' => $file,
            ),
        ));
    }

    public function testGetFullFile()
    {
        $file = array('globalId' => 1, 'hashId' => 'testHashId');

        $this->mockUploadFile($file);

        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', array(
            array(
                'functionName' => 'getProductResource',
                'returnValue' => array(
                    'no' => 'test no',
                    'reskey' => 'test reskey',
                    'size' => 123,
                    'name' => 'test name',
                ),
            ),
        ));

        $result = $this->getSupplierFileImplementor()->getFullFile($file);
        $mockedS2B2C->shouldHaveReceived('getProductResource')->times(1);

        $expected = array(
            'globalId' => 1,
            'hashId' => 'testHashId',
            'convertParams' => array(),
            'metas2' => array(),
            'storage' => 'supplier',
            'no' => 'test no',
            'reskey' => 'test reskey',
            'size' => 123,
            'name' => 'test name',
        );

        $this->assertEquals($expected, $result);
    }

    public function testGetFileByGlobalId()
    {
        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', array(
            array(
                'functionName' => 'getProductResource',
                'returnValue' => array(
                    'no' => 'test no',
                    'reskey' => 'test reskey',
                    'size' => 123,
                    'name' => 'test name',
                ),
            ),
        ));

        $this->mockUploadFile(array('globalId' => 1, 'hashId' => 'testHashId'));

        $result = $this->getSupplierFileImplementor()->getFileByGlobalId(1);
        $mockedS2B2C->shouldHaveReceived('getProductResource')->times(1);

        $expected = array(
            'globalId' => 1,
            'hashId' => 'testHashId',
            'convertParams' => array(),
            'metas2' => array(),
            'storage' => 'supplier',
            'no' => 'test no',
            'reskey' => 'test reskey',
            'size' => 123,
            'name' => 'test name',
        );

        $this->assertEquals($expected, $result);
    }

    public function testPlayer()
    {
        $this->mockUploadFile(array('globalId' => 1, 'hashId' => 'testHashId'));

        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', array(
            array(
                'functionName' => 'getProductResourcePlayer',
                'returnValue' => array(
                    'testPlayer' => 'test player',
                ),
            ),
        ));

        $result = $this->getSupplierFileImplementor()->player(1);
        $mockedS2B2C->shouldHaveReceived('getProductResourcePlayer')->times(1);

        $this->assertEquals(array('testPlayer' => 'test player'), $result);
    }

    public function testGetDownloadFile()
    {
        $mockedS2B2C = $this->mockPureBiz('qiQiuYunSdk.s2b2cService', array(
            array(
                'functionName' => 'getProductResDownload',
                'returnValue' => array(
                    'testDownload' => 'test download',
                ),
            ),
        ));

        $file = array('globalId' => 1, 'hashId' => 'testHashId');
        $this->mockUploadFile($file);

        $result = $this->getSupplierFileImplementor()->getDownloadFile($file);
        $mockedS2B2C->shouldHaveReceived('getProductResDownload')->times(1);

        $expected = array(
            'testDownload' => 'test download',
            'type' => 'url',
        );

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
