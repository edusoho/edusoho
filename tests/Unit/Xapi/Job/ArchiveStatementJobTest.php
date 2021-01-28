<?php

namespace Tests\Unit\Xapi\Job;

use Biz\Xapi\Job\ArchiveStatementJob;
use Biz\BaseTestCase;

class ArchiveStatementJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $job = new ArchiveStatementJob(array(), $this->getBiz());
        $this->mockBiz('Xapi:XapiService', array(
            array('functionName' => 'archiveStatement', 'returnValue' => null),
        ));
        $this->assertNull($job->execute());
    }
}
