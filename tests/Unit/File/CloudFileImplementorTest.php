<?php

namespace Tests\Unit\File;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\CloudPlatform\Client\FailoverCloudAPI;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\File\Service\Impl\CloudFileImplementorImpl;

class CloudFileImplementorTest extends BaseTestCase
{
    public function testMoveFile()
    {
        $result = $this->getCloudFileImplementor()->moveFile('materiallibtest', '1');
        $this->assertNull($result);
    }

    public function testGetFile()
    {
        $result = $this->getCloudFileImplementor()->getFile([
            'convertParams' => [],
            'metas' => '{}',
            'metas2' => '{"a":"1"}',
        ]);
        $this->assertEquals([], $result['convertParams']);
        $this->assertEquals([], $result['metas']);
        $this->assertEquals(['a' => 1], $result['metas2']);
    }

    public function testGetFullFile()
    {
        $api = CloudAPIFactory::create('leaf', 'v1');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'test.file',
            'type' => 'video',
            'processStatus' => 'none',
        ]);

        $this->getCloudFileImplementor()->setApi('leaf', $mockObject, 'v1');
        $result = $this->getCloudFileImplementor()->getFullFile(['globalId' => 1]);
        $this->assertEquals('cloud', $result['storage']);
        $this->assertEquals('noneed', $result['convertStatus']);
    }

    public function testGetFileByGlobalId()
    {
        $this->mockBiz('File:UploadFileDao', [
            ['functionName' => 'getByGlobalId', 'returnValue' => ['globalId' => 1]],
        ]);

        $api = CloudAPIFactory::create('root', 'v1');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'test.file',
            'type' => 'video',
            'processStatus' => 'none',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject, 'v1');
        $result = $this->getCloudFileImplementor()->getFileByGlobalId(1);
        $this->assertEquals('cloud', $result['storage']);
        $this->assertEquals('noneed', $result['convertStatus']);
    }

    public function testAddFile()
    {
        $fileInfo = [
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'size' => 10,
            'globalId' => 1,
        ];
        $result = $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
        $this->assertEquals('testmode-1/test.mp4', $result['hashId']);
        $this->assertEquals(1, $result['globalId']);
    }

    public function testAddFileWithLazyParam()
    {
        $fileInfo = [
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'size' => 10,
            'lazyConvert' => true,
            'convertParams' => ['test' => 'HD'],
            'globalId' => 1,
        ];
        $result = $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
        $this->assertEquals('testmode-1/test.mp4', $result['hashId']);
        $this->assertEquals(1, $result['globalId']);
        $this->assertEquals('lazy-testmode-1/test.mp4', $result['convertHash']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testAddFileWithError1()
    {
        $fileInfo = [
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'globalId' => 1,
        ];
        $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.globalId_required
     */
    public function testAddFileWithError2()
    {
        $fileInfo = [
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'size' => 10,
        ];
        $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
    }

    public function testReconvert()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn([
            'num' => 10,
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->reconvert(1, []);
        $this->assertEquals(['num' => 10], $result);
    }

    public function testRetryTranscode()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn([
            'success' => true,
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->retryTranscode([1, 2, 3, 4]);
        $this->assertEquals(['success' => true], $result);
    }

    public function testGetResourcesStatus()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'status' => 'ok',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getResourcesStatus(['cursor' => 'true']);
        $this->assertEquals(['status' => 'ok'], $result);
        $result = $this->getCloudFileImplementor()->getResourcesStatus([]);
        $this->assertEquals([], $result);
    }

    public function testGetAudioServiceStatus()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'success' => false,
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getAudioServiceStatus();
        $this->assertEquals(['success' => false], $result);
    }

    public function testDeleteFile()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('delete')->times(1)->andReturn([
            'success' => true,
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->deleteFile(['globalId' => 1]);
        $this->assertEquals(['success' => true], $result);
    }

    public function testPlayer()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'success' => true,
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);
        $result = $this->getCloudFileImplementor()->player(1, true);
        $this->assertEquals(['success' => true], $result);
    }

    public function testUpdateFile()
    {
        $this->mockBiz('File:UploadFileDao', [
            ['functionName' => 'getByGlobalId', 'returnValue' => ['globalId' => 1]],
        ]);
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn([
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'testnew.file',
            'type' => 'ppt',
            'processStatus' => 'none',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);
        $result = $this->getCloudFileImplementor()->updateFile(1, ['name' => 'testnew.file']);
        $this->assertEquals('testnew.file', $result['name']);
    }

    public function testUpdateFileWithFalse()
    {
        $this->mockBiz('File:UploadFileDao', [
            ['functionName' => 'getByGlobalId', 'returnValue' => ['globalId' => 1]],
        ]);
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn([
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'testnew.file',
            'processStatus' => 'none',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);
        $result = $this->getCloudFileImplementor()->updateFile(0, ['name' => 'testnew.file']);
        $this->assertFalse($result);
    }

    public function testPrepareUpload()
    {
        $params = [
            'name' => 'test.mp4',
            'targetId' => 1,
            'targetType' => 'testmode',
        ];
        $result = $this->getCloudFileImplementor()->prepareUpload($params);
        $this->assertEquals('test.mp4', $result['filename']);
        $this->assertEquals('uploading', $result['status']);
        $this->assertEquals('mp4', $result['ext']);
    }

    public function testInitUpload()
    {
        $params = [
            [
                'functionName' => 'startUpload',
                'runTimes' => 1,
                'returnValue' => [
                    'no' => 1,
                    'uploadUrl' => '/test.mp4',
                    'uploadMode' => 'test',
                    'uploadToken' => '123456',
                    'reskey' => 'test',
                ],
            ],
        ];
        $this->mockBiz('CloudPlatform:ResourceFacadeService', $params);

        $file = [
            'id' => 1,
            'bucket' => 'test',
            'hashId' => 'test/test.mp4',
            'hash' => '123456',
            'name' => 'test.mp4',
            'fileSize' => 10,
            'directives' => [],
            'type' => 'video',
        ];
        $file['targetType'] = 'attachment';
        $result = $this->getCloudFileImplementor()->initUpload($file);
        $this->assertEquals('test/test.mp4', $result['hashId']);
        $this->assertEquals('123456', $result['uploadToken']);

        $file['targetType'] = 'subtitle';
        $result = $this->getCloudFileImplementor()->initUpload($file);
        $this->assertEquals('test/test.mp4', $result['hashId']);
        $this->assertEquals('123456', $result['uploadToken']);
    }

    public function testGetUploadAuth()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn([
            'url' => 'http://www.test.com/upload',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getUploadAuth(['globalId' => 1]);
        $this->assertEquals('http://www.test.com/upload', $result['url']);
    }

    public function testResumeUpload()
    {
        $params = [
            [
                'functionName' => 'resumeUpload',
                'runTimes' => 1,
                'returnValue' => [
                    'no' => 1,
                    'resumed' => 'ok',
                    'uploadUrl' => '/test.mp4',
                    'uploadMode' => 'test',
                    'uploadToken' => '123456',
                    'reskey' => 'test',
                ],
            ],
        ];
        $this->mockBiz('CloudPlatform:ResourceFacadeService', $params);

        $file = [
            'globalId' => 1,
            'id' => 1,
            'bucket' => 'test',
            'hashId' => 'test/test.mp4',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
            'directives' => [],
            'type' => 'video',
        ];
        $file['targetType'] = 'attachment';
        $result = $this->getCloudFileImplementor()->resumeUpload($file, [
            'bucket' => 'test',
            'hash' => '123456',
            'name' => 'test.mp4',
            'fileSize' => 10,
        ]);
        $this->assertEquals('test/test.mp4', $result['hashId']);
        $this->assertEquals('123456', $result['uploadToken']);
    }

    public function testResumeUploadWithNull()
    {
        $params = [
            [
                'functionName' => 'resumeUpload',
                'runTimes' => 1,
                'returnValue' => [
                    'no' => 1,
                    'uploadUrl' => '/test.mp4',
                    'uploadMode' => 'test',
                    'uploadToken' => '123456',
                ],
            ],
        ];
        $this->mockBiz('CloudPlatform:ResourceFacadeService', $params);

        $file = [
            'globalId' => 1,
            'id' => 1,
            'bucket' => 'test',
            'hashId' => 'test/test.mp4',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
            'directives' => [],
            'type' => 'video',
        ];
        $file['targetType'] = 'attachment';
        $result = $this->getCloudFileImplementor()->resumeUpload($file, [
            'bucket' => 'test',
            'hash' => '123456',
            'name' => 'test.mp4',
            'fileSize' => 10,
        ]);
        $this->assertNull($result);
    }

    public function testDownload()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'url' => 'http://download',
        ]);

        $this->getCloudFileImplementor()->setApi('leaf', $mockObject);

        $result = $this->getCloudFileImplementor()->download(1);
        $this->assertEquals('http://download', $result['url']);
    }

    public function testGetDownloadFile()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'url' => 'http://download',
        ]);

        $this->getCloudFileImplementor()->setApi('leaf', $mockObject);

        $result = $this->getCloudFileImplementor()->getDownloadFile(1, true);
        $this->assertEquals('http://download', $result['url']);
        $this->assertEquals('url', $result['type']);
    }

    public function testGetDefaultHumbnails()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'images' => 'http://download/images',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getDefaultHumbnails(1);
        $this->assertEquals(['images' => 'http://download/images'], $result);
    }

    public function testGetDefaultHumbnailsWithEmpty()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'images' => 'http://download/images',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getDefaultHumbnails(0);
        $this->assertEquals([], $result);
    }

    public function testGetThumbnail()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'images' => 'http://download/images',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getThumbnail(0, []);
        $this->assertEquals(['images' => 'http://download/images'], $result);
    }

    public function testGetStatistics()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'num' => '1',
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getStatistics([]);
        $this->assertEquals(['num' => '1'], $result);
    }

    public function testFindFilesWithEmpty()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'data' => [],
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->findFiles([], []);
        $this->assertEmpty($result);
    }

    public function testFindFilesWithCloudEmpty()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'data' => [],
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->findFiles([1 => ['globalId' => 1]], []);
        $this->assertEquals([1 => ['globalId' => 1]], $result);
    }

    public function testFindFiles()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'data' => [
                1 => [
                    'no' => 1,
                    'reskey' => 'test',
                    'size' => 100,
                    'name' => 'test.file',
                    'processStatus' => 'none',
                ],
            ],
            'count' => 1,
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->findFiles([1 => ['globalId' => 1]], []);
        $this->assertEquals(1, count($result));
    }

    public function testFinishedUpload()
    {
        $params = [
            [
                'functionName' => 'finishUpload',
                'runTimes' => 1,
                'returnValue' => [],
            ],
            [
                'functionName' => 'getResource',
                'runTimes' => 1,
                'returnValue' => [
                    'no' => 1,
                    'reskey' => 'test',
                    'size' => 100,
                    'name' => 'test.file',
                    'processStatus' => 'none',
                    'length' => '10',
                ],
            ],
        ];
        $this->mockBiz('CloudPlatform:ResourceFacadeService', $params);

        $result = $this->getCloudFileImplementor()->finishedUpload([
            'globalId' => 1,
            'id' => 1,
            'targetType' => 'attachment',
        ], [
            'length' => 10,
            'size' => 10,
            'filename' => 'test.mp4',
        ]);
        $this->assertEquals(10, $result['length']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.globalId_required
     */
    public function testFinishedUploadWithNoGlobalId()
    {
        $this->getCloudFileImplementor()->finishedUpload([], ['globalId' => 0]);
    }

    public function testSearch()
    {
        $this->mockBiz('File:UploadFileDao', [
            ['functionName' => 'findByIds', 'returnValue' => [1 => ['globalId' => 1]]],
        ]);
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn([
            'data' => [
                1 => [
                    'no' => 1,
                    'reskey' => 'test',
                    'size' => 100,
                    'extno' => 2,
                    'name' => 'test.file',
                    'type' => 'ppt',
                    'processStatus' => 'none',
                ],
            ],
        ]);

        $this->getCloudFileImplementor()->setApi('root', $mockObject, 'v1');

        $result = $this->getCloudFileImplementor()->search(['id' => 1]);
        $this->assertEquals(1, count($result['data']));
    }

    public function testDeleteMP4Files()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(['success' => 'ok']);

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->deleteMP4Files('callback');

        $this->assertEquals(['success' => 'ok'], $result);
    }

    public function testCreateApi()
    {
        $class = new CloudFileImplementorImpl($this->getBiz());
        $result = ReflectionUtils::invokeMethod($class, 'createApi', ['root']);
        $this->assertEquals(true, $result instanceof FailoverCloudAPI);
    }

    public function testProccessConvertParamsAndMetasWithType()
    {
        $class = new CloudFileImplementorImpl($this->getBiz());
        $result = ReflectionUtils::invokeMethod($class, 'proccessConvertParamsAndMetas', [
            [
                'directives' => ['output' => ['output' => true], 'watermarks' => []],
                'type' => 'video',
            ],
        ]);

        $this->assertEquals('video', $result['type']);
    }

    /**
     * @return CloudFileImplementorImpl
     */
    protected function getCloudFileImplementor()
    {
        return $this->getBiz()->service('File:CloudFileImplementor');
    }
}
