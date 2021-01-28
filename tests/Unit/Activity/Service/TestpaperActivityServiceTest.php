<?php

namespace Tests\Unit\Activity\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ArrayToolkit;

class TestpaperActivityServiceTest extends BaseTestCase
{
    public function testGetActivity()
    {
        $activity = $this->createActivity();

        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['id']);

        $this->assertArrayEquals($activity, $testpaperActivity);
    }

    public function testFindActivitiesByIds()
    {
        $activity1 = $this->createActivity();
        $activity2 = $this->createActivity2();
        $activity3 = $this->createActivity3();

        $activities = $this->getTestpaperActivityService()->findActivitiesByIds(array($activity2['id'], $activity3['id']));
        $activities = ArrayToolkit::index($activities, 'id');

        $this->assertEquals(2, count($activities));
    }

    public function testFindActivitiesByMediaIds()
    {
        $activity1 = $this->createActivity();
        $activity2 = $this->createActivity2();
        $activity3 = $this->createActivity4();

        $mediaIds = array($activity1['mediaId'], $activity2['mediaId'], $activity3['mediaId']);
        $activities = $this->getTestpaperActivityService()->findActivitiesByIds($mediaIds);

        $this->assertEquals(2, count($activities));
    }

    public function testCreateActivity()
    {
        $fields = array(
            'mediaId' => 1,
            'doTimes' => 0,
            'redoInterval' => 0,
            'limitedTime' => 0,
            'checkType' => 'score',
            'finishCondition' => array(
                'type' => 'score',
                'finishScore' => 5,
            ),
            'testMode' => 'normal',
        );
        $activity = $this->getTestpaperActivityService()->createActivity($fields);
        $this->assertArrayEquals($fields['finishCondition'], $activity['finishCondition']);
    }

    public function testUpdateActivity()
    {
        $activity = $this->createActivity();
        $updateFields = array(
            'doTimes' => 1,
            'limitedTime' => 5,
        );

        $update = $this->getTestpaperActivityService()->updateActivity($activity['id'], $updateFields);

        $this->assertEquals($updateFields['doTimes'], $update['doTimes']);
        $this->assertEquals($updateFields['limitedTime'], $update['limitedTime']);
    }

    public function testDeleteActivity()
    {
        $activity = $this->createActivity();
        $this->getTestpaperActivityService()->deleteActivity($activity['id']);
        $activity = $this->getTestpaperActivityService()->getActivity($activity['id']);

        $this->assertNull($activity);
    }

    protected function createActivity()
    {
        $fields = array(
            'mediaId' => 1,
            'doTimes' => 0,
            'redoInterval' => 0,
            'limitedTime' => 0,
            'checkType' => 'score',
            'finishCondition' => array(
                'type' => 'score',
                'finishScore' => 5,
            ),
            'testMode' => 'normal',
        );

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    protected function createActivity2()
    {
        $fields = array(
            'mediaId' => 1,
            'doTimes' => 0,
            'redoInterval' => '0.1',
            'limitedTime' => 0,
            'checkType' => 'score',
            'finishCondition' => array(
                'type' => 'score',
                'finishScore' => 5,
            ),
            'testMode' => 'realTime',
        );

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    protected function createActivity3()
    {
        $fields = array(
            'mediaId' => 1,
            'doTimes' => 1,
            'redoInterval' => 0,
            'limitedTime' => 5,
            'checkType' => 'score',
            'finishCondition' => array(
                'type' => 'score',
            ),
            'testMode' => 'normal',
        );

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    protected function createActivity4()
    {
        $fields = array(
            'mediaId' => 2,
            'doTimes' => 1,
            'redoInterval' => 0,
            'limitedTime' => 5,
            'checkType' => 'score',
            'finishCondition' => array(
                'type' => 'score',
            ),
            'testMode' => 'normal',
        );

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }
}
