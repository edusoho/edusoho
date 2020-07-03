<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class LiveActivityDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $activity1 = $this->mockDataObject(['mediaId' => 1]);
        $activity2 = $this->mockDataObject(['mediaId' => 2]);
        $results = $this->getDao()->findByIds([1, 2]);

        $this->assertEquals(2, $results[1]['mediaId']);
    }

    public function testFindByLiveIdAndReplayStatus()
    {
        $activity1 = $this->mockDataObject(['liveId' => 1, 'replayStatus' => 'ungenerated']);
        $activity2 = $this->mockDataObject(['liveId' => 1, 'replayStatus' => 'videoGenerated']);
        $results = $this->getDao()->findByLiveIdAndReplayStatus(1);

        $this->assertEquals(1, count($results));
    }

    public function testGetByLiveId()
    {
        $activity1 = $this->mockDataObject(['liveId' => 1, 'replayStatus' => 'ungenerated']);
        $activity2 = $this->mockDataObject(['liveId' => 2, 'replayStatus' => 'videoGenerated']);
        $results = $this->getDao()->getByLiveId(1);

        $this->assertEquals('ungenerated', $results['replayStatus']);
    }

    public function testGetBySyncIdGTAndLiveId()
    {
        $activity1 = $this->mockDataObject(['liveId' => 1, 'syncId' => 0]);
        $activity2 = $this->mockDataObject(['liveId' => 2, 'syncId' => 2]);
        $results = $this->getDao()->getBySyncIdGTAndLiveId(1);

        $this->assertEquals(0, count($results['replayStatus']));
    }

    public function testGetBySyncId()
    {
        $activity1 = $this->mockDataObject(['liveId' => 1, 'syncId' => 0]);
        $activity2 = $this->mockDataObject(['liveId' => 2, 'syncId' => 2]);
        $results = $this->getDao()->getBySyncId(2);

        $this->assertEquals(2, $results['liveId']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'liveId' => 1,
            'liveProvider' => 1,
            'replayStatus' => 'generated',
            'mediaId' => 1,
            'roomCreated' => 1,
        ];
    }
}
