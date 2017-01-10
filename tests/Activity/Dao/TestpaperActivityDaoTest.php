<?php

namespace Tests\Activity\Dao;

use Tests\Base\BaseDaoTestCase;

class TestpaperActivityDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $activity1 = array_merge($this->getDefaultMockFields(), array('id' => 1));
        $activity2 = array_merge($this->getDefaultMockFields(), array('id' => 2));
        $expectedActivity1 = $this->getTestpaperActivityDao()->create($activity1);
        $expectedActivity2 = $this->getTestpaperActivityDao()->create($activity2);

        $testConditons = array(
            array(
                'condition' => array('id' => 1),
                'expectedResults' => array($expectedActivity1),
                'expectedCount' => 1
            ),
            array(
                'condition' => array('ids' => array(1, 2)),
                'expectedResults' => array($expectedActivity1,$expectedActivity2),
                'expectedCount' => 2
            )
        );

        $this->searchTestUtil($this->getTestpaperActivityDao(), $testConditons, array_keys($activity1));
    }

    public function testfindActivitiesByIds()
    {
        $activity1 = array_merge($this->getDefaultMockFields(), array('id' => 1));
        $activity2 = array_merge($this->getDefaultMockFields(), array('id' => 2));
        $expectedActivity1 = $this->getTestpaperActivityDao()->create($activity1);
        $expectedActivity2 = $this->getTestpaperActivityDao()->create($activity2);
        $expectedResults = array($expectedActivity1,$expectedActivity2);
        $ids = array(1, 2);

        $results = $this->getTestpaperActivityDao()->findActivitiesByIds($ids);
        foreach ($results as $key => $result) {
            $this->assertArrayEquals($result, $expectedResults[$key], array_keys($activity1));
        }
    }

    protected function getDefaultMockFields()
    {
        return array(
            'mediaId' => 1,
            'doTimes' => 1,
            'redoInterval' => 1.1,
            'limitedTime' => 1,
            'checkType' => 'ss',
            'finishCondition' => 'ss',
            'requireCredit' => 1,
            'testMode' => 's'
        );
    }

    protected function getTestpaperActivityDao()
    {
        return $this->getBiz()->dao('Activity:TestpaperActivityDao');
    }
}
