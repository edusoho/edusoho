<?php

namespace Tests\Unit\File\Job;

use Biz\BaseTestCase;
use Biz\File\Job\VideoMediaStatusUpdateJob;

class VideoMediaStatusUpdateJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $mockRemoteResources = array(
            'data' => array(
                array(
                    'resourceNo' => 1,
                    'status' => 'ok',
                    'audio' => true,
                    'mp4' => true,
                ),
            ),
            'next' => array('cursor' => time(), 'start' => 0, 'limit' => 1),
        );

        $this->mockBiz('File:UploadFileService');
        $this->getUploadFileService()->shouldReceive('getResourcesStatus')->andReturn($mockRemoteResources);
        $this->getUploadFileService()->shouldReceive('setResourceConvertStatus')->andReturn(array());

        $job = new VideoMediaStatusUpdateJob(array('args' => $mockRemoteResources['next']), $this->getBiz());
        $result = $job->execute();
        $this->assertNull($result);

        $this->getUploadFileService()->shouldHaveReceived('getResourcesStatus');
        $this->getUploadFileService()->shouldHaveReceived('setResourceConvertStatus');
    }

    public function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
