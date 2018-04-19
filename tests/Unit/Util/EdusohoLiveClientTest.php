<?php

namespace Tests\Unit\Util;

use Biz\BaseTestCase;
use Biz\Util\EdusohoLiveClient;
use Biz\CloudPlatform\CloudAPIFactory;
use Mockery;

class EdusohoLiveClientlTest extends BaseTestCase
{
    public function testCreateLive()
    {
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('id' => 123, 'provider' => 6));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $fields = array(
            'title' => 'live title',
            'startTime' => time() + 60,
            'summary' => '',
            'endTime' => time() + 600,
        );
        $result = $client->createLive($fields);

        $this->assertEquals(123, $result['id']);
        $this->assertEquals(6, $result['provider']);
    }

    public function testUpdateLive()
    {
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('patch')->times(1)->andReturn(array('id' => 123, 'title' => 'live title update', 'provider' => 6));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $result = $client->updateLive(array('liveId' => 123, 'title' => 'live title update'));

        $this->assertEquals(123, $result['id']);
        $this->assertEquals('live title update', $result['title']);
    }

    public function testGetCapacity()
    {
        $cloudApi = CloudAPIFactory::create('leaf');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('capacity' => 10, 'provider' => 6));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'leaf');

        $result = $client->getCapacity();

        $this->assertEquals(10, $result['capacity']);
        $this->assertEquals(6, $result['provider']);
    }

    public function testGetRoomUrl()
    {
        $cloudApi = CloudAPIFactory::create('leaf');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('url' => 'http://www.edusoho.com'));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'leaf');

        $result = $client->getRoomUrl(array('liveId' => 123), 'leaf');

        $this->assertEquals('http://www.edusoho.com', $result['url']);
    }

    public function testDeleteLive()
    {
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('delete')->times(1)->andReturn(array('success' => true));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $result = $client->deleteLive(123, 6);
        $this->assertTrue($result['success']);
    }

    public function testGetMaxOnline()
    {
        $cloudApi = CloudAPIFactory::create('leaf');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('onLineNum' => 5));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'leaf');

        $result = $client->getMaxOnline(123);
        $this->assertEquals(5, $result['onLineNum']);
    }

    public function testEntryLive()
    {
        $cloudApi = CloudAPIFactory::create('leaf');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('success' => true));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'leaf');

        $result = $client->entryLive(array('liveId' => 123));
        $this->assertTrue($result['success']);
    }

    public function testEntryReplay()
    {
        $cloudApi = CloudAPIFactory::create('leaf');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('device' => 'iphone', 'url' => 'http://www.edusoho.com'));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'leaf');

        $result = $client->entryReplay(array('liveId' => 123));
        $this->assertEquals('iphone', $result['device']);
        $this->assertEquals('http://www.edusoho.com', $result['url']);
    }

    public function testCreateReplayList()
    {
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array(array('subject' => 'name1', 'id' => 1, 'resourceNo' => '333')));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $result = $client->createReplayList(123, 'name1', 6);
        $this->assertEquals('name1', $result[0]['subject']);
        $this->assertEquals('333', $result[0]['resourceNo']);
    }

    public function testIsAvailableRecord()
    {
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('success' => 1));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $result = $client->isAvailableRecord(123);
        $this->assertTrue($result);
    }

    public function testSetLiveLogo()
    {
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('post')->times(1)->andReturn(array('logoPcUrl' => '123', 'logoClientUrl' => '456', 'logoGotoUrl' => '789'));
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $result = $client->setLiveLogo(array('logoPcUrl' => '123', 'logoClientUrl' => '456', 'logoGotoUrl' => '789'));

        $this->assertEquals(123, $result['logoPcUrl']);
        $this->assertEquals(456, $result['logoClientUrl']);
        $this->assertEquals(789, $result['logoGotoUrl']);
    }

    public function testGetLiveAccount()
    {
        $return = array('provider' => 9, 'capacity' => 10, 'roomType' => array('large'));
        $cloudApi = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($cloudApi);
        $mockObject->shouldReceive('get')->times(1)->andReturn($return);
        $client = new EdusohoLiveClient();
        $client->setCloudApi($mockObject, 'root');

        $result = $client->getLiveAccount();

        $this->assertArrayEquals($return, $result);
    }
}
