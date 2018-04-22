<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ThreadByPostIdDataTag;

class ThreadByPostIdDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyPostId()
    {
        $dataTag = new ThreadByPostIdDataTag();
        $announcement = $dataTag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new ThreadByPostIdDataTag();
        $threads = $datatag->getData(array('postId' => 1));
        $this->assertNull($threads);

        $this->mockBiz('Thread:ThreadService', array(
            array(
                'functionName' => 'getPost',
                'returnValue' => array('id' => 1, 'title' => 'post title', 'threadId' => 1, 'targetId' => 1, 'targetType' => 'classroom'),
            ),
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'title' => 'thread title', 'targetId' => 1, 'targetType' => 'classroom'),
            ),
        ));

        $thread = $datatag->getData(array('courseId' => 1, 'postId' => 1));
        $this->assertEquals(1, $thread['id']);
    }
}
