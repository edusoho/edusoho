<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseThreadByPostIdDataTag;

class CourseThreadByPostIdDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyPostId()
    {
        $dataTag = new CourseThreadByPostIdDataTag();
        $announcement = $dataTag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new CourseThreadByPostIdDataTag();
        $threads = $datatag->getData(array('courseId' => 1,'postId' => 1));
        $this->assertNull($threads);

        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getPost',
                'returnValue' => array('id' => 1, 'title' => 'post title', 'threadId' => 1, 'courseId' => 1)
            ),
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'title' => 'thread title', 'courseId' => 1)
            )
        ));

        $thread = $datatag->getData(array('courseId' => 1,'postId' => 1));
        $this->assertEquals(1, $thread['id']);
    }
}
