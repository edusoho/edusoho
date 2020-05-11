<?php

namespace Tests\Unit\Activity\Copy;

use Biz\BaseTestCase;

class ActivityCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $result = $this->getActivityCopy()->preCopy(array(), array());

        $this->assertNull($result);
    }

    public function testDoCopy()
    {
        $this->mockBiz('Testpaper:TestpapeDao', array(
            array('functionName' => 'getTestpaperByCopyIdAndCourseSetId', 'returnValue' => array('id' => 1)),
        ));

        $this->mockBiz('Activity:ActivityDao', array(
            array('functionName' => 'findByCourseId', 'returnValue' => array(
                array(
                    'id' => 1,
                    'mediaType' => 'video',
                    'title' => 'test title',
                    'remark' => 'test remark',
                    'content' => 'test content',
                    'length' => 20,
                    'mediaId' => 1,
                    'startTime' => time() - 3600,
                    'endTime' => time(),
                    'finishData' => array()
                ),
                array(
                    'id' => 2,
                    'mediaType' => 'video',
                    'title' => 'test title',
                    'remark' => 'test remark',
                    'content' => 'test content',
                    'length' => 30,
                    'mediaId' => 2,
                    'startTime' => time() - 3600,
                    'endTime' => time(),
                    'finishData' => array()
                ),
                array(
                    'id' => 3,
                    'mediaType' => 'testpaper',
                    'title' => 'test title',
                    'remark' => 'test remark',
                    'content' => 'test content',
                    'length' => 30,
                    'mediaId' => 2,
                    'startTime' => time() - 3600,
                    'endTime' => time(),
                    'finishData' => array()
                ),
            )),
            array('functionName' => 'get', 'returnValue' => array(
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            )),
            array('functionName' => 'create', 'returnValue' => array(
                'id' => 1,
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            )),
        ));

        $this->mockBiz('Activity:TestpaperActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => array(
                'mediaId' => 1,
                'doTimes' => 33,
                'redoInterval' => 33,
                'limitedTime' => time(),
                'checkType' => null,
                'finishCondition' => array(),
                'requireCredit' => 0,
                'testMode' => 'normal',
                'answerSceneId' => 1,
            )),
            array('functionName' => 'createActivity', 'returnValue' => array(
                'id' => 1,
                'mediaId' => 1,
                'doTimes' => 33,
                'redoInterval' => 33,
                'limitedTime' => time(),
                'checkType' => null,
                'finishCondition' => array(),
                'requireCredit' => 0,
                'testMode' => 'normal',
            )),
        ));

        $this->mockBiz('ItemBank:Answer:AnswerSceneService', array(
            array('functionName' => 'get', 'returnValue' => array(
                'id' => 1,
                'limited_time' => 1,
                'redoInterval' => 1,
                'limitedTime' => 1,
                'enable_facein' => 1,
                'redo_interval' => 1,
                'do_times' => 1,
            )),
            array('functionName' => 'create', 'returnValue' => array(
                'id' => 1,
                'limited_time' => 1,
                'redoInterval' => 1,
                'limitedTime' => 1,
                'enable_facein' => 1,
                'redo_interval' => 1,
                'do_times' => 1,
            )),
        ));

        $this->mockBiz('Activity:VideoActivityDao', array(
            array('functionName' => 'get', 'returnValue' => array(
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            )),
            array('functionName' => 'create', 'returnValue' => array(
                'id' => 1,
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            )),
        ));

        $result = $this->getActivityCopy()->doCopy(array(), array(
            'originCourse' => array('id' => 1),
            'newCourse' => array('id' => 2),
            'newCourseSet' => array('id' => 2),
            'newActivity' => array('id' => 2),
            'originActivity' => array('id' => 1),
        ));

        $this->assertNull($result);
    }

    protected function getActivityCopy($params = array())
    {
        return new \Biz\Activity\Copy\ActivityCopy($this->biz, $params);
    }
}
