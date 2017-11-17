<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TestpaperActivityDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expectedActivity1 = $this->mockDataObject();
        $expectedActivity2 = $this->mockDataObject();

        $testConditons = array(
            array(
                'condition' => array('id' => 1),
                'expectedResults' => array($expectedActivity1),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('ids' => array(1, 2)),
                'expectedResults' => array($expectedActivity1, $expectedActivity2),
                'expectedCount' => 2,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditons, $this->getCompareKeys());
    }

    public function testfindActivitiesByIds()
    {
        $expectedActivity1 = $this->mockDataObject();
        $expectedActivity2 = $this->mockDataObject();
        $expectedResults = array($expectedActivity1, $expectedActivity2);
        $ids = array(1, 2);

        $results = $this->getDao()->findByIds($ids);
        foreach ($results as $key => $result) {
            $this->assertArrayEquals($result, $expectedResults[$key], $this->getCompareKeys());
        }
    }

    public function testFindByMediaIds()
    {
        $expectedActivity1 = $this->mockDataObject();
        $expectedActivity2 = $this->mockDataObject(array('mediaId' => 2));
        $results = $this->getDao()->findByMediaIds(array(1, 2));

        $this->assertEquals(count($results), 2);
        $this->assertArrayEquals($expectedActivity1, $results[0], $this->getCompareKeys());
        $this->assertArrayEquals($expectedActivity2, $results[1], $this->getCompareKeys());
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
            'testMode' => 's',
        );
    }
}
