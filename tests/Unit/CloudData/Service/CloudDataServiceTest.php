<?php

namespace Tests\Unit\CloudData\Service;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Mockery;

class CloudDataServiceTest extends BaseTestCase
{
    public function testPush()
    {
        $api = CloudAPIFactory::create('event');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('push')->times(1)->andReturn(array('success' => 'ok'));

        $this->getCloudDataService()->setCloudApi($mockObject);

        $result = $this->getCloudDataService()->push('school.thread_post.delete', array(), time(), 'normal');

        $this->assertEquals(array('success' => 'ok'), $result);
    }

    public function testPushWithError()
    {
        $api = CloudAPIFactory::create('event');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('push')->times(1)->andThrow('Exception', '事件发送失败');

        $this->getCloudDataService()->setCloudApi($mockObject);

        $timestamp = time();
        $result = $this->getCloudDataService()->push('school.thread_post.delete', array(), $timestamp, 'normal');

        $condition = array(
            'name' => 'school.thread_post.delete',
            'timestamp' => $timestamp,
            'createdUserId' => $this->getCurrentUser()->id,
        );

        $cloudDataCount = $this->getCloudDataDao()->count($condition);

        $this->assertFalse($result);
        $this->assertEquals(0, $cloudDataCount);
    }

    public function testPushWithErrorAndCreateData()
    {
        $api = CloudAPIFactory::create('event');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('push')->times(1)->andThrow('Exception', '事件发送失败');

        $this->getCloudDataService()->setCloudApi($mockObject);

        $data = $this->getPushData();

        $timestamp = time();
        $result = $this->getCloudDataService()->push('school.thread_post.delete', $data, $timestamp, 'important');

        $condition = array(
            'name' => 'school.thread_post.delete',
            'timestamp' => $timestamp,
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $cloudDataCount = $this->getCloudDataDao()->count($condition);
        $cloudDatas = $this->getCloudDataDao()->search($condition, array('createdTime' => 'ASC'), 0, $cloudDataCount);

        $this->assertFalse($result);
        $this->assertEquals(1, $cloudDataCount);
        $this->assertEquals(1, count($cloudDatas));
        $this->assertEquals($data, $cloudDatas[0]['body']);
    }

    public function testSearchCloudDataCount()
    {
        $timestamp = time();
        $fields = array(
            'name' => 'school.thread_post.delete',
            'body' => $this->getPushData(),
            'timestamp' => $timestamp,
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $condition = array(
            'name' => 'school.thread_post.delete',
            'timestamp' => $timestamp,
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $result = $this->getCloudDataService()->searchCloudDataCount($condition);
        $this->assertEquals(0, $result);

        $this->getCloudDataDao()->create($fields);
        $result = $this->getCloudDataService()->searchCloudDataCount($condition);
        $this->assertEquals(1, $result);
    }

    public function testSearchCloudDatas()
    {
        $timestamp = time();
        $fields = array(
            'name' => 'school.thread_post.delete',
            'body' => $this->getPushData(),
            'timestamp' => $timestamp,
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $condition = array(
            'name' => 'school.thread_post.delete',
            'timestamp' => $timestamp,
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $result = $this->getCloudDataService()->searchCloudDatas($condition, array('createdTime' => 'ASC'), 0, 1);
        $this->assertEquals(0, count($result));

        $this->getCloudDataDao()->create($fields);
        $cloudDataCount = $this->getCloudDataDao()->count($condition);
        $result = $this->getCloudDataService()->searchCloudDatas($condition, array('createdTime' => 'ASC'), 0, $cloudDataCount);

        $this->assertEquals(1, count($result));
        $this->assertEquals($this->getPushData(), $result[0]['body']);
    }

    public function testDeleteCloudData()
    {
        $fields = array(
            'name' => 'school.thread_post.delete',
            'body' => $this->getPushData(),
            'timestamp' => time(),
            'createdUserId' => $this->getCurrentUser()->getId(),
        );

        $cloudData = $this->getCloudDataDao()->create($fields);

        $result = $this->getCloudDataDao()->get($cloudData['id']);
        $this->assertEquals($this->getPushData(), $result['body']);

        $this->getCloudDataService()->deleteCloudData($cloudData['id']);

        $result = $this->getCloudDataDao()->get($cloudData['id']);
        $this->assertNull($result);
    }

    private function getPushData()
    {
        return array(
            'id' => 1,
            'threadId' => 1,
            'content' => 'good',
            'userId' => 5,
            'target' => array('type' => 'course', 'id' => 1),
            'thread' => array(),
            'createdTime' => time(),
            'postType' => 'content',
        );
    }

    protected function getCloudDataService()
    {
        return $this->biz->service('CloudData:CloudDataService');
    }

    protected function getCloudDataDao()
    {
        return $this->biz->dao('CloudData:CloudDataDao');
    }
}
