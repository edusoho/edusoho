<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ThreadDataTag;

class ThreadDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyThreadId()
    {
        $dataTag = new ThreadDataTag();
        $announcement = $dataTag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new ThreadDataTag();
        $threads = $datatag->getData(array('threadId' => 1));
        $this->assertNull($threads);

        $this->mockBiz('Thread:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'title' => 'thread title', 'targetId' => 1, 'targetType' => 'classroom'),
            ),
        ));

        $thread = $datatag->getData(array('threadId' => 1));
        $this->assertEquals(1, $thread['id']);
    }
}
