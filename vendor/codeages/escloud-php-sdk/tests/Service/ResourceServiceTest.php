<?php

namespace ESCloud\SDK\Tests\Service;

use ESCloud\SDK\Service\ResourceService;
use ESCloud\SDK\Tests\BaseTestCase;

class ResourceServiceTest extends BaseTestCase
{
    public function testStartUpload()
    {
        $mock = ['no' => 'fc8ea8c24d7945da86b9d49a82ee16b7', 'uploadUrl' => '//upload.qiqiuyun.net', 'reskey' => '1577089429/5e007995f3d5794220468', 'uploadToken' => 'test_token_1'];
        $httpClient = $this->mockHttpClient($mock);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->startUpload(['name' => 'test.mp4', 'extno' => 'test_extno_1']);

        $this->assertEquals($mock['no'], $result['no']);
        $this->assertEquals($mock['uploadUrl'], $result['uploadUrl']);
        $this->assertEquals($mock['reskey'], $result['reskey']);
    }


    public function testFinishUpload()
    {
        $resource = $this->mockResource();
        $httpClient = $this->mockHttpClient($resource);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->finishUpload($resource['no']);

        $this->assertEquals($resource['no'], $result['no']);
        $this->assertEquals($resource['extno'], $result['extno']);
        $this->assertEquals($resource['name'], $result['name']);
    }

    public function testGet()
    {
        $resource = $this->mockResource();
        $httpClient = $this->mockHttpClient($resource);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->get($resource['no']);

        $this->assertEquals($resource['no'], $result['no']);
        $this->assertEquals($resource['extno'], $result['extno']);
        $this->assertEquals($resource['name'], $result['name']);
    }

    public function testSearch()
    {
        $resource = $this->mockResource();
        $httpClient = $this->mockHttpClient([$resource]);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->search([]);

        $this->assertEquals($resource['no'], $result[0]['no']);
        $this->assertEquals($resource['extno'], $result[0]['extno']);
        $this->assertEquals($resource['name'], $result[0]['name']);
    }

    public function testGetDownloadUrl()
    {
        $resource = $this->mockResource();
        $httpClient = $this->mockHttpClient(['downloadUrl' => 'http://downloadUrl.qiqiuyun.net']);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->getDownloadUrl($resource['no']);

        $this->assertNotNull($result['downloadUrl']);
    }

    public function testRename()
    {
        $resource = $this->mockResource();
        $httpClient = $this->mockHttpClient($resource);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->rename($resource['no'], 'test.mp4');

        $this->assertEquals($resource['no'], $result['no']);
        $this->assertEquals($resource['extno'], $result['extno']);
        $this->assertEquals($resource['name'], $result['name']);
    }

    public function testDelete()
    {
        $resource = $this->mockResource();
        $httpClient = $this->mockHttpClient(['success' => true]);
        $service = new ResourceService($this->auth, array(), null, $httpClient);
        $result = $service->delete($resource['no']);

        $this->assertTrue($result['success']);
    }

    private function mockResource()
    {
        return array(
            'no' => 'test_no_1',
            'extno' => 'test_extno_1',
            'name' => 'test.mp4',
            'type' => 'video',
            'size' => 5201314,
            'length' => 3600,
            'thumbnail' => '',
            'processStatus' => '2333',
            'isShare' => 1,
            'processedTime' => 1548915578,
            'createdTime' => 1548915578,
            'updatedTIme' => 1548915578
        );
    }
}
