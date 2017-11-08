<?php

namespace Tests\Unit\Activity;

use Biz\BaseTestCase;
use Biz\Activity\Service\DownloadActivityService;

class DownloadActivityServiceTest extends BaseTestCase
{
    public function testDownloadActivityFile()
    {
        $this->mockBiz(
            'Activity:ActivityService',
            array(
                array(
                    'functionName' => 'getActivity',
                    'returnValue' => array('id' => 11, 'fromCourseId' => 12),
                    'withParams' => array(22, true),
                ),
            )
        );

        $this->mockBiz(
            'Course:MaterialService',
            array(
                array(
                    'functionName' => 'getMaterial',
                    'returnValue' => array('id' => 111, 'fileId' => 111, 'link' => 'test'),
                    'withParams' => array(12, 33),
                ),
            )
        );

        $result = $this->getDownloadActivityService()->downloadActivityFile(22, 33);

        $this->assertEquals(array('id' => 111, 'fileId' => 111, 'link' => 'test'), $result);
    }

    protected function getDownloadActivityService()
    {
        return $this->createService('Activity:DownloadActivityService');
    }
}
