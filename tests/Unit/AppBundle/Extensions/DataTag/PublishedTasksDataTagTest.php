<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PublishedTasksDataTag;

class PublishedTasksDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function checkEmptyArgumentError()
    {
        $dataTag = new PublishedTasksDataTag();
        $dataTag->getData();
    }

    public function testGetDataHasCount()
    {
        $service = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'searchTasks',
                'returnValue' => array(array('id' => 1), array('id' => 2), array('id' => 3)),
            ),
        ));

        $dataTag = new PublishedTasksDataTag();
        $data = $dataTag->getData(array('courseSetId' => 1, 'count' => 5));

        $this->assertEquals(3, count($data));
        $service->shouldHaveReceived('searchTasks')->times(1);
    }

    public function testGetDataHasType()
    {
        $service = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'countTasks',
                'returnValue' => 2,
            ),
            array(
                'functionName' => 'searchTasks',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
        ));

        $dataTag = new PublishedTasksDataTag();
        $data = $dataTag->getData(array('courseSetId' => 1, 'type' => 'text'));

        $this->assertEquals(2, count($data));
        $service->shouldHaveReceived('countTasks')->times(1);
        $service->shouldHaveReceived('searchTasks')->times(1);
    }
}
