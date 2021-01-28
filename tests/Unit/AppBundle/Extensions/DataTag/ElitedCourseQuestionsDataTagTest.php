<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ElitedCourseQuestionsDataTag;

class ElitedCourseQuestionsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentMissing()
    {
        $datatag = new ElitedCourseQuestionsDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCourseIdMissing()
    {
        $datatag = new ElitedCourseQuestionsDataTag();
        $datatag->getData(array('countId' => 5));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new ElitedCourseQuestionsDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'searchThreads',
                'returnValue' => array(array('id' => 1, 'type' => 'question', 'isElite' => 1)),
            ),
        ));
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 1, 'title' => 'course title'),
            ),
        ));

        $datatag = new ElitedCourseQuestionsDataTag();
        $result = $datatag->getData(array('courseId' => 1, 'count' => 5));
        $this->assertNotNull($result['courses']);
    }
}
