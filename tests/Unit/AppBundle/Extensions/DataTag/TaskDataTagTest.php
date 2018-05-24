<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\TaskDataTag;

class TaskDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyArguments()
    {
        $datatag = new TaskDataTag();
        $datatag->getData(array());
    }

    public function testGetData()
    {
        $mockData = array('id' => 1, 'title' => 'task name');
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'getTask',
                'returnValue' => $mockData,
            ),
        ));
        $dataTag = new TaskDataTag();
        $result = $dataTag->getData(array('taskId' => 1));

        $this->assertArrayEquals($mockData, $result);
    }
}
