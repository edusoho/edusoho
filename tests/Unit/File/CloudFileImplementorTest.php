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
        $result = $this->getCloudFileImplementor()->getFile(array(
            'convertParams' => array(),
            'metas' => '{}',
            'metas2' => '{"a":"1"}',
        ));
        $this->assertEquals(array(), $result['convertParams']);
        $this->assertEquals(array(), $result['metas']);
        $this->assertEquals(array('a' => 1), $result['metas2']);
    }

    public function testGetFullFile()
    {
        $api = CloudAPIFactory::create('leaf', 'v1');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'test.file',
            'type' => 'video',
            'processStatus' => 'none',
        ));

        $this->getCloudFileImplementor()->setApi('leaf', $mockObject, 'v1');
        $result = $this->getCloudFileImplementor()->getFullFile(array('globalId' => 1));
        $this->assertEquals('cloud', $result['storage']);
        $this->assertEquals('noneed', $result['convertStatus']);
    }

    public function testGetFileByGlobalId()
    {
        $this->mockBiz('File:UploadFileDao', array(
            array('functionName' => 'getByGlobalId', 'returnValue' => array('globalId' => 1)),
        ));

        $api = CloudAPIFactory::create('root', 'v1');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'test.file',
            'type' => 'video',
            'processStatus' => 'none',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject, 'v1');
        $result = $this->getCloudFileImplementor()->getFileByGlobalId(1);
        $this->assertEquals('cloud', $result['storage']);
        $this->assertEquals('noneed', $result['convertStatus']);
    }

    public function testAddFile()
    {
        $fileInfo = array(
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'size' => 10,
            'globalId' => 1,
        );
        $result = $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
        $this->assertEquals('testmode-1/test.mp4', $result['hashId']);
        $this->assertEquals(1, $result['globalId']);
    }

    public function testAddFileWithLazyParam()
    {
        $fileInfo = array(
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'size' => 10,
            'lazyConvert' => true,
            'convertParams' => array('test' => 'HD'),
            'globalId' => 1,
        );
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
        $fileInfo = array(
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'globalId' => 1,
        );
        $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.globalId_required
     */
    public function testAddFileWithError2()
    {
        $fileInfo = array(
            'filename' => 'test.mp4',
            'key' => 'testmode-1/test.mp4',
            'size' => 10,
        );
        $this->getCloudFileImplementor()->addFile('testmode', 1, $fileInfo);
    }

    public function testReconvert()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'num' => 10,
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->reconvert(1, array());
        $this->assertEquals(array('num' => 10), $result);
    }

    public function testRetryTranscode()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'success' => true,
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->retryTranscode(array(1, 2, 3, 4));
        $this->assertEquals(array('success' => true), $result);
    }

    public function testGetResourcesStatus()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'status' => 'ok',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getResourcesStatus(array('cursor' => 'true'));
        $this->assertEquals(array('status' => 'ok'), $result);
        $result = $this->getCloudFileImplementor()->getResourcesStatus(array());
        $this->assertEquals(array(), $result);
    }

    public function testGetAudioServiceStatus()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'success' => false,
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getAudioServiceStatus();
        $this->assertEquals(array('success' => false), $result);
    }

    public function testDeleteFile()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('delete')->times(1)->andReturn(array(
            'success' => true,
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->deleteFile(array('globalId' => 1));
        $this->assertEquals(array('success' => true), $result);
    }

    public function testPlayer()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'success' => true,
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);
        $result = $this->getCloudFileImplementor()->player(1, true);
        $this->assertEquals(array('success' => true), $result);
    }

    public function testUpdateFile()
    {
        $this->mockBiz('File:UploadFileDao', array(
            array('functionName' => 'getByGlobalId', 'returnValue' => array('globalId' => 1)),
        ));
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'testnew.file',
            'type' => 'ppt',
            'processStatus' => 'none',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);
        $result = $this->getCloudFileImplementor()->updateFile(1, array('name' => 'testnew.file'));
        $this->assertEquals('testnew.file', $result['name']);
    }

    public function testUpdateFileWithFalse()
    {
        $this->mockBiz('File:UploadFileDao', array(
            array('functionName' => 'getByGlobalId', 'returnValue' => array('globalId' => 1)),
        ));
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'testnew.file',
            'processStatus' => 'none',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);
        $result = $this->getCloudFileImplementor()->updateFile(0, array('name' => 'testnew.file'));
        $this->assertFalse($result);
    }

    public function testPrepareUpload()
    {
        $params = array(
            'fileName' => 'test.mp4',
            'targetId' => 1,
            'targetType' => 'testmode',
        );
        $result = $this->getCloudFileImplementor()->prepareUpload($params);
        $this->assertEquals('test.mp4', $result['filename']);
        $this->assertEquals('uploading', $result['status']);
        $this->assertEquals('mp4', $result['ext']);
    }

    public function testInitUpload()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'no' => 1,
            'uploadUrl' => '/test.mp4',
            'uploadMode' => 'test',
            'uploadToken' => '123456',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $file = array(
            'id' => 1,
            'bucket' => 'test',
            'hashId' => 'test/test.mp4',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
            'directives' => array(),
            'type' => 'video',
        );
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
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'url' => 'http://www.test.com/upload',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getUploadAuth(array('globalId' => 1));
        $this->assertEquals('http://www.test.com/upload', $result['url']);
    }

    public function testResumeUpload()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'no' => 1,
            'resumed' => 'ok',
            'uploadUrl' => '/test.mp4',
            'uploadMode' => 'test',
            'uploadToken' => '123456',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $file = array(
            'globalId' => 1,
            'id' => 1,
            'bucket' => 'test',
            'hashId' => 'test/test.mp4',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
            'directives' => array(),
            'type' => 'video',
        );
        $file['targetType'] = 'attachment';
        $result = $this->getCloudFileImplementor()->resumeUpload($file, array(
            'bucket' => 'test',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
        ));
        $this->assertEquals('test/test.mp4', $result['hashId']);
        $this->assertEquals('123456', $result['uploadToken']);
    }

    public function testResumeUploadWithNull()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(
            'no' => 1,
            'uploadUrl' => '/test.mp4',
            'uploadMode' => 'test',
            'uploadToken' => '123456',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $file = array(
            'globalId' => 1,
            'id' => 1,
            'bucket' => 'test',
            'hashId' => 'test/test.mp4',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
            'directives' => array(),
            'type' => 'video',
        );
        $file['targetType'] = 'attachment';
        $result = $this->getCloudFileImplementor()->resumeUpload($file, array(
            'bucket' => 'test',
            'hash' => '123456',
            'fileName' => 'test.mp4',
            'fileSize' => 10,
        ));
        $this->assertNull($result);
    }

    public function testDownload()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'url' => 'http://download',
        ));

        $this->getCloudFileImplementor()->setApi('leaf', $mockObject);

        $result = $this->getCloudFileImplementor()->download(1);
        $this->assertEquals('http://download', $result['url']);
    }

    public function testGetDownloadFile()
    {
        $api = CloudAPIFactory::create('leaf');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'url' => 'http://download',
        ));

        $this->getCloudFileImplementor()->setApi('leaf', $mockObject);

        $result = $this->getCloudFileImplementor()->getDownloadFile(1, true);
        $this->assertEquals('http://download', $result['url']);
        $this->assertEquals('url', $result['type']);
    }

    public function testGetDefaultHumbnails()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'images' => 'http://download/images',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getDefaultHumbnails(1);
        $this->assertEquals(array('images' => 'http://download/images'), $result);
    }

    public function testGetDefaultHumbnailsWithEmpty()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'images' => 'http://download/images',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getDefaultHumbnails(0);
        $this->assertEquals(array(), $result);
    }

    public function testGetThumbnail()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'images' => 'http://download/images',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getThumbnail(0, array());
        $this->assertEquals(array('images' => 'http://download/images'), $result);
    }

    public function testGetStatistics()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'num' => '1',
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->getStatistics(array());
        $this->assertEquals(array('num' => '1'), $result);
    }

    public function testFindFilesWithEmpty()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'data' => array(),
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->findFiles(array(), array());
        $this->assertEmpty($result);
    }

    public function testFindFilesWithCloudEmpty()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'data' => array(),
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->findFiles(array(1 => array('globalId' => 1)), array());
        $this->assertEquals(array(1 => array('globalId' => 1)), $result);
    }

    public function testFindFiles()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'data' => array(
                1 => array(
                    'no' => 1,
                    'reskey' => 'test',
                    'size' => 100,
                    'name' => 'test.file',
                    'processStatus' => 'none',
                ),
            ),
            'count' => 1,
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->findFiles(array(1 => array('globalId' => 1)), array());
        $this->assertEquals(1, count($result));
    }

    public function testFinishedUpload()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'no' => 1,
            'reskey' => 'test',
            'size' => 100,
            'name' => 'test.file',
            'processStatus' => 'none',
            'length' => '10',
        ));
        $mockObject->shouldReceive('post')->times(1)->andReturn(array());

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->finishedUpload(array(
            'globalId' => 1,
            'id' => 1,
            'targetType' => 'attachment',
        ), array(
            'length' => 10,
            'size' => 10,
            'filename' => 'test.mp4',
        ));
        $this->assertEquals(10, $result['length']);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.globalId_required
     */
    public function testFinishedUploadWithNoGlobalId()
    {
        $this->getCloudFileImplementor()->finishedUpload(array(), array('globalId' => 0));
    }

    public function testSearch()
    {
        $this->mockBiz('File:UploadFileDao', array(
            array('functionName' => 'findByIds', 'returnValue' => array(1 => array('globalId' => 1))),
        ));
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array(
            'data' => array(
                1 => array(
                    'no' => 1,
                    'reskey' => 'test',
                    'size' => 100,
                    'extno' => 2,
                    'name' => 'test.file',
                    'type' => 'ppt',
                    'processStatus' => 'none',
                ),
            ),
        ));

        $this->getCloudFileImplementor()->setApi('root', $mockObject, 'v1');

        $result = $this->getCloudFileImplementor()->search(array('id' => 1));
        $this->assertEquals(1, count($result['data']));
    }

    public function testDeleteMP4Files()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = \Mockery::mock($api);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => 'ok'));

        $this->getCloudFileImplementor()->setApi('root', $mockObject);

        $result = $this->getCloudFileImplementor()->deleteMP4Files('callback');

        $this->assertEquals(array('success' => 'ok'), $result);
    }

    public function testCreateApi()
    {
        $class = new CloudFileImplementorImpl($this->getBiz());
        $result = ReflectionUtils::invokeMethod($class, 'createApi', array('root'));
        $this->assertEquals(true, $result instanceof FailoverCloudAPI);
    }

    public function testProccessConvertParamsAndMetasWithType()
    {
        $class = new CloudFileImplementorImpl($this->getBiz());
        $result = ReflectionUtils::invokeMethod($class, 'proccessConvertParamsAndMetas', array(
            array(
                'directives' => array('output' => array('output' => true), 'watermarks' => array()),
                'type' => 'video',
            ),
        ));

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
