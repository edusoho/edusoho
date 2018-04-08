<?php

namespace Tests\Unit\Testpaper\Job;

use Biz\Testpaper\Job\UpdateRealTimeTestResultStatusJob;
use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class UpdateRealTimeTestResultStatusJobTest extends BaseTestCase
{
    public function testArgsEmpty()
    {
        $job = new UpdateRealTimeTestResultStatusJob(array('args' => array('targetId' => 1)), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);

        $job = new UpdateRealTimeTestResultStatusJob(array('args' => array('targetId' => 1, 'activity' => 'task')), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);

        $job = new UpdateRealTimeTestResultStatusJob(array('args' => array('targetId' => 1, 'activity' => 'activity')), $this->biz);
        $result = $job->execute();
        $this->assertNull($result);
    }

    public function testExecute()
    {
        $args = array('args' => array('targetId' => 1, 'targetType' => 'activity'));
        $job = new UpdateRealTimeTestResultStatusJob($args, $this->biz);
        
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array(
                    'id' => 1,
                    'fromCourseId' => 2,
                    'mediaType' => 'testpaper',
                    'ext' => array(
                            'testMode' => 'realTime',
                            'limitedTime' => 100,
                            'mediaId' => 5,
                        )
                ),
            )
        ));

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'searchTestpaperResults',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
            array(
                'functionName' => 'searchItemCount',
                'returnValue' => 1,
            ),
            array(
                'functionName' => 'updateTestpaperResult',
                'returnValue' => array(),
            )  
        ));
        $result = $job->execute();

        $this->assertTrue(true);
    }

    public function testActivityTypeError()
    {
        $args = array('args' => array('targetId' => 1, 'targetType' => 'activity'));
        $job = new UpdateRealTimeTestResultStatusJob($args, $this->biz);
        
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array(
                    'id' => 1,
                    'fromCourseId' => 2,
                    'mediaType' => 'text',
                    'ext' => array()
                ),
            )
        ));

        $result = $job->execute();

        $this->assertNull($result);
    }

    public function testTestpaperResultsEmpty()
    {
        $args = array('args' => array('targetId' => 1, 'targetType' => 'activity'));
        $job = new UpdateRealTimeTestResultStatusJob($args, $this->biz);
        
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array(
                    'id' => 1,
                    'fromCourseId' => 2,
                    'mediaType' => 'testpaper',
                    'ext' => array(
                            'testMode' => 'realTime',
                            'limitedTime' => 100,
                            'mediaId' => 5,
                        )
                ),
            )
        ));

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'searchTestpaperResults',
                'returnValue' => array(),
            ), 
        ));

        $result = $job->execute();

        $this->assertNull($result);
    }

}
