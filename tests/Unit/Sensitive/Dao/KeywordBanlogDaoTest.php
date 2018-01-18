<?php

namespace Tests\Unit\Sensitive\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class KeywordBanlogDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3, 'state' => 'banned'));
        $expected[] = $this->mockDataObject(array('keywordId' => 3, 'keywordName' => 'testName'));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('id' => 1),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('userId' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('state' => 'banned'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('keywordId' => 3),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('keywordName' => 'test'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('keyword' => 3, 'searchBanlog' => 'id'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('keyword' => 'test', 'searchBanlog' => 'name'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testSearchBanlogsByUserIds()
    {
        $result = $this->getDao()->searchBanlogsByUserIds(array(), array(), 0, 5);
        $this->assertEquals(array(), $result);

        $expected = $this->mockDataObject();
        $expected = $this->mockDataObject(array('userId' => 3));
        $result = $this->getDao()->searchBanlogsByUserIds(array(2, 3), array(), 0, 5);
        $this->assertEquals(2, $result[1]['userId']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'keywordId' => 2,
            'keywordName' => 'name',
            'state' => 'replaced',
            'text' => 'text',
            'userId' => 2,
            'ip' => '127.0.0.1',
            'createdTime' => 0,
        );
    }
}
