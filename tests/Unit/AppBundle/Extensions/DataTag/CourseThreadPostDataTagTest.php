<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseThreadPostDataTag;
use Biz\BaseTestCase;

class CourseThreadPostDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyPostId()
    {
        $dataTag = new CourseThreadPostDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new CourseThreadPostDataTag();
        $threadPost = $datatag->getData(array('courseId' => 1, 'postId' => 1));
        $this->assertNull($threadPost);

        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'getPost',
                'returnValue' => array('id' => 1, 'threadId' => 2, 'courseId' => 1),
            ),
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'title' => 'course title'),
            ),
        ));


        $threadPost = $datatag->getData(array('courseId' => 1, 'postId' => 1));
        $this->assertEquals(1, $threadPost['id']);
        $this->assertEquals(1, $threadPost['thread']['id']);
    }

}