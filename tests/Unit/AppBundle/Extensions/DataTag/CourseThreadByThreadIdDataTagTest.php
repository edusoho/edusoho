<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseThreadByThreadIdDataTag;

class CourseThreadByThreadIdDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyThreadId()
    {
        $dataTag = new CourseThreadByThreadIdDataTag();
        $announcement = $dataTag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new CourseThreadByThreadIdDataTag();
        $threads = $datatag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertNull($threads);

        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'title' => 'thread content', 'courseId' => 1),
            ),
        ));

        $thread = $datatag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertEquals(1, $thread['id']);
    }
}
