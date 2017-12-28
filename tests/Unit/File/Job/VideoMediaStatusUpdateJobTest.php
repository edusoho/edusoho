<?php

namespace Tests\Unit\File\Job;

use Biz\BaseTestCase;
use Biz\File\Job\VideoMediaStatusUpdateJob;

class VideoMediaStatusUpdateJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('File:UploadFileService', array(
            array(
                'functionName' => 'getResourcesStatus',
                'returnValue' => array('data' => array(), 'next' => array('cursor' => 0, 'start' => 0, 'limit' => 1000)),
            ),
        ));

        $job = new VideoMediaStatusUpdateJob(array('args' => array('cursor' => 0, 'start' => 0, 'limit' => 1)), $this->getBiz());
        $result = $job->execute();

        $this->assertNull($result);
    }
}
