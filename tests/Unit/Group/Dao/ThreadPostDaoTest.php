<?php

namespace Tests\Unit\Group\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadPostDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 4, 'postId' => 4));
        $expected[] = $this->mockDataObject(array('adopt' => 4, 'threadId' => 3));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('id' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('userId' => 4),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('postId' => 4),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('adopt' => 4),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('threadId' => 3),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testSearchPostsThreadIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('threadId' => 4));
        $result = $this->getDao()->searchPostsThreadIds(array('id' => 3), array('id' => 'DESC'), 0, 5);
        $this->assertEquals(4, $result[0]['threadId']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testSearchPostsThreadIdsWithErrorOrderBysField()
    {
        $this->getDao()->searchPostsThreadIds(array('id' => 3), array('userId' => 'DESC'), 0, 5);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Dao\DaoException
     */
    public function testSearchPostsThreadIdsWithErrorOrderBysDirection()
    {
        $this->getDao()->searchPostsThreadIds(array('id' => 3), array('id' => 'DE'), 0, 5);
    }

    public function testCountPostsThreadIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('threadId' => 4));
        $result = $this->getDao()->countPostsThreadIds(array('id' => 3));
        $this->assertEquals(2, $result);
    }

    public function testDeleteByThreadId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('threadId' => 4));
        $this->getDao()->deleteByThreadId(4);
        $result = $this->getDao()->countPostsThreadIds(array('id' => 3));
        $this->assertEquals(1, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'threadId' => 2,
            'content' => 'content',
            'userId' => 3,
            'fromUserId' => 4,
            'postId' => 3,
            'adopt' => 5,
        );
    }
}
