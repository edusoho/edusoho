<?php

namespace Tests\Unit\CloudPlatform\Service;

use Biz\BaseTestCase;

class ResourceFacadeServiceTest extends BaseTestCase
{
    public function testAgentInWhiteList()
    {
        $result = $this->getResourceFacadeService()->agentInWhiteList('iPhone');
        $this->assertTrue($result);
        $result = $this->getResourceFacadeService()->agentInWhiteList('iPad');
        $this->assertTrue($result);
        $result = $this->getResourceFacadeService()->agentInWhiteList('Android');
        $this->assertTrue($result);
        $result = $this->getResourceFacadeService()->agentInWhiteList('HTC');
        $this->assertTrue($result);
        $result = $this->getResourceFacadeService()->agentInWhiteList('HuaWei');
        $this->assertFalse($result);
    }

    public function testGetFrontPlaySDKPathByType()
    {
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('player');
        $this->assertNotFalse(strstr($result, 'js-sdk/sdk-v1.js'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('newPlayer');
        $this->assertNotFalse(strstr($result, 'js-sdk/sdk-v2.js'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('audio');
        $this->assertNotFalse(strstr($result, 'js-sdk-v2/sdk-v1.js'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('video');
        $this->assertNotFalse(strstr($result, 'js-sdk-v2/sdk-v1.js'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('uploader');
        $this->assertNotFalse(strstr($result, 'js-sdk-v2/uploader/sdk-2.1.0.js'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('old_uploader');
        $this->assertNotFalse(strstr($result, 'js-sdk/uploader/sdk-v1.js'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('old_document');
        $this->assertNotFalse(strstr($result, 'js-sdk/document-player/v7/viewer.html'));
        $result = $this->getResourceFacadeService()->getFrontPlaySDKPathByType('faq');
        $this->assertNotFalse(strstr($result, 'js-sdk/faq/sdk-v1.js'));
    }

    public function testGetPlayerContext()
    {
        $mockObject = \Mockery::mock();
        $mockObject->shouldReceive('makePlayToken')->times(1)->andReturn('test token');
        $this->biz['ESCloudSdk.play'] = $mockObject;

        $file = [
            'type' => 'video',
            'globalId' => 'test',
            'storage' => 'cloud',
            'convertStatus' => 'success',
        ];
        $playContext = $this->getResourceFacadeService()->getPlayerContext($file);
        $this->assertEquals('test token', $playContext['token']);
        $this->assertEquals('test', $playContext['resNo']);

        $file = [
            'type' => 'audio',
            'globalId' => 'test',
            'storage' => 'cloud',
            'convertStatus' => 'success',
        ];
        $playContext = $this->getResourceFacadeService()->getPlayerContext($file);
        $this->assertEquals('audio-player', $playContext['jsPlayer']);

        $file = [
            'type' => 'ppt',
            'globalId' => 'test',
            'storage' => 'cloud',
            'convertStatus' => 'success',
        ];
        $playContext = $this->getResourceFacadeService()->getPlayerContext($file);
        $this->assertEquals('test token', $playContext['token']);
    }

    protected function getResourceFacadeService()
    {
        return $this->createService('CloudPlatform:ResourceFacadeService');
    }
}
