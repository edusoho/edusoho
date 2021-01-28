<?php

namespace Tests\Unit\Group\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadGoodsDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('fileId' => 4, 'postId' => 4));
        $expected[] = $this->mockDataObject(array('threadId' => 4, 'type' => 'attachment'));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('threadId' => 4),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('fileId' => 4),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('postId' => 4),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('type' => 'attachment'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testDeleteByThreadIdAndType()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->deleteByThreadIdAndType(3, 'content');
        $result = $this->getDao()->get(1);
        $this->assertNull($result);
    }

    public function testSumGoodsCoins()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('coin' => 4));
        $result = $this->getDao()->sumGoodsCoins(array('threadId' => 3));
        $this->assertEquals(9, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => 'title',
            'userId' => 2,
            'type' => 'content',
            'threadId' => 3,
            'postId' => 3,
            'coin' => 5,
            'fileId' => 3,
            'hitNum' => 5,
            'createdTime' => 10000,
        );
    }
}
