<?php

namespace Tests\Unit\Activity\Copy;

use Biz\BaseTestCase;

class ActivityCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $result = $this->getActivityCopy()->preCopy([], []);

        $this->assertNull($result);
    }

    public function testDoCopy()
    {
        $this->mockBiz('Testpaper:TestpapeDao', [
            ['functionName' => 'getTestpaperByCopyIdAndCourseSetId', 'returnValue' => ['id' => 1]],
        ]);

        $this->mockBiz('Activity:ActivityDao', [
            ['functionName' => 'findByCourseId', 'returnValue' => [
                [
                    'id' => 1,
                    'mediaType' => 'video',
                    'title' => 'test title',
                    'remark' => 'test remark',
                    'content' => 'test content',
                    'length' => 20,
                    'mediaId' => 1,
                    'startTime' => time() - 3600,
                    'endTime' => time(),
                    'finishData' => [],
                ],
                [
                    'id' => 2,
                    'mediaType' => 'video',
                    'title' => 'test title',
                    'remark' => 'test remark',
                    'content' => 'test content',
                    'length' => 30,
                    'mediaId' => 2,
                    'startTime' => time() - 3600,
                    'endTime' => time(),
                    'finishData' => [],
                ],
                [
                    'id' => 3,
                    'mediaType' => 'testpaper',
                    'title' => 'test title',
                    'remark' => 'test remark',
                    'content' => 'test content',
                    'length' => 30,
                    'mediaId' => 2,
                    'startTime' => time() - 3600,
                    'endTime' => time(),
                    'finishData' => [],
                ],
            ]],
            ['functionName' => 'get', 'returnValue' => [
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            ]],
            ['functionName' => 'create', 'returnValue' => [
                'id' => 1,
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            ]],
        ]);

        $this->mockBiz('Activity:TestpaperActivityService', [
            ['functionName' => 'getActivity', 'returnValue' => [
                'mediaId' => 1,
                'doTimes' => 33,
                'redoInterval' => 33,
                'limitedTime' => time(),
                'checkType' => null,
                'finishCondition' => [],
                'requireCredit' => 0,
                'testMode' => 'normal',
                'answerSceneId' => 1,
            ]],
            ['functionName' => 'createActivity', 'returnValue' => [
                'id' => 1,
                'mediaId' => 1,
                'doTimes' => 33,
                'redoInterval' => 33,
                'limitedTime' => time(),
                'checkType' => null,
                'finishCondition' => [],
                'requireCredit' => 0,
                'testMode' => 'normal',
            ]],
        ]);

        $this->mockBiz('ItemBank:Answer:AnswerSceneService', [
            ['functionName' => 'get', 'returnValue' => [
                'id' => 1,
                'limited_time' => 1,
                'redoInterval' => 1,
                'limitedTime' => 1,
                'enable_facein' => 1,
                'redo_interval' => 1,
                'do_times' => 1,
            ]],
            ['functionName' => 'create', 'returnValue' => [
                'id' => 1,
                'limited_time' => 1,
                'redoInterval' => 1,
                'limitedTime' => 1,
                'enable_facein' => 1,
                'redo_interval' => 1,
                'do_times' => 1,
            ]],
        ]);

        $this->mockBiz('Activity:VideoActivityDao', [
            ['functionName' => 'get', 'returnValue' => [
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            ]],
            ['functionName' => 'create', 'returnValue' => [
                'id' => 1,
                'mediaSource' => 'self',
                'mediaId' => 1,
                'mediaUri' => '',
                'finishType' => 0,
                'finishDetail' => 1,
            ]],
        ]);

        $result = $this->getActivityCopy()->doCopy([], [
            'originCourse' => ['id' => 1],
            'newCourse' => ['id' => 2],
            'newCourseSet' => ['id' => 2],
            'newActivity' => ['id' => 2],
            'originActivity' => ['id' => 1],
        ]);

        $this->assertNull($result);
    }

    protected function getActivityCopy($params = [])
    {
        return new \Biz\Activity\Copy\ActivityCopy($this->biz, $params);
    }
}
