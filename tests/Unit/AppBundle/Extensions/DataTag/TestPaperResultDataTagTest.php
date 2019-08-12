<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\TestPaperResultDataTag;
use Biz\BaseTestCase;

class TestPaperResultDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testEmptyArguments()
    {
        $dataTag = new TestPaperResultDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $dataTag = new TestPaperResultDataTag();

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'fromCourseId' => 1, 'mediaType' => 'homework'),
            ),
        ));

        $testpaper = $dataTag->getData(array('activityId' => 1, 'testpaperId' => 1));
        $this->assertEquals(1, $testpaper['id']);
    }
}