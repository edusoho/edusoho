<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PublishedLivingTasksDataTag;

class PublishedLivingTasksDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCheckEmptyArgumentError()
    {
        $dataTag = new PublishedLivingTasksDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $service = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'findPublishedLivingTasksByCourseSetId',
                'returnValue' => array(array('id' => 1), array('id' => 2), array('id' => 3)),
            ),
        ));

        $dataTag = new PublishedLivingTasksDataTag();
        $data = $dataTag->getData(array('courseSetId' => 1, 'count' => 5));

        $this->assertEquals(3, count($data));
        $service->shouldHaveReceived('findPublishedLivingTasksByCourseSetId')->times(1);
    }
}
