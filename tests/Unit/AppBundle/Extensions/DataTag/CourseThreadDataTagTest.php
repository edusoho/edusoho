<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseThreadDataTag;

class CourseThreadDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCourseId()
    {
        $dataTag = new CourseThreadDataTag();
        $announcement = $dataTag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyThreadId()
    {
        $dataTag = new CourseThreadDataTag();
        $announcement = $dataTag->getData(array('courseId' => 1));
    }

    public function testGetData()
    {
        $datatag = new CourseThreadDataTag();
        $threads = $datatag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertNull($threads);

        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'title' => 'thread content', 'courseId' => 1),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'course title'),
            ),
        ));

        $thread = $datatag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertEquals(1, $thread['id']);
        $this->assertEquals(1, $thread['course']['id']);
    }
}
