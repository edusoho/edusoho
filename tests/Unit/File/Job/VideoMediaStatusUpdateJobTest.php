<?php

namespace Tests\Unit\File\Job;

use Biz\BaseTestCase;
use Biz\File\Job\VideoMediaStatusUpdateJob;

class VideoMediaStatusUpdateJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $mockRemoteResources = [
            'data' => [
                [
                    'resourceNo' => 1,
                    'status' => 'ok',
                    'audio' => true,
                    'mp4' => true,
                ],
            ],
            'next' => ['cursor' => time(), 'start' => 0, 'limit' => 1],
        ];

        $this->mockBiz(
            'File:UploadFileService',
            [
                [
                    'functionName' => 'getResourcesStatus',
                    'returnValue' => $mockRemoteResources,
                    'withParams' => [$mockRemoteResources['next']],
                ],
                [
                    'functionName' => 'setResourceConvertStatus',
                    'withParams' => [1, $mockRemoteResources['data'][0]],
                ],
                [
                    'functionName' => 'setAttachmentConvertStatus',
                    'withParams' => [1, $mockRemoteResources['data'][0]],
                ],
            ]
        );

        $job = new VideoMediaStatusUpdateJob(['args' => $mockRemoteResources['next']], $this->getBiz());
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
