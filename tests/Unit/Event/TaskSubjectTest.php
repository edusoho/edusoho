<?php

namespace Tests\Unit\Event;

use Biz\BaseTestCase;
use Biz\Event\Service\Impl\TaskSubject;

class TaskSubjectTest extends BaseTestCase
{
    public function testEmptyArgument()
    {
        $subject = new TaskSubject($this->biz);
        $result = $subject->getSubject(0);

        $this->assertNull($result);
    }

    public function testGetSubject()
    {
        $subject = new TaskSubject($this->biz);

        $user = $this->getCurrentUser();
        $mockResult = array('id' => 1, 'title' => 'task title');
        $taskService = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTask',
                'returnValue' => $mockResult,
            ),
        ));
        $result = $subject->getSubject(1);

        $this->assertArrayEquals($mockResult, $result);
        $taskService->shouldHaveReceived('getTask');
    }
}
