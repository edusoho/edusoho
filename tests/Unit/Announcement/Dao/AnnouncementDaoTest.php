<?php

namespace Tests\Unit\Announcement\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class AnnouncementDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('startTime' => 200, 'content' => 'char'));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'endTime' => 300, 'orgCode' => 'char'));
        $expected[] = $this->mockDataObject(array('targetType' => 'int', 'targetId' => 2, 'copyId' => 2));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('startTime' => 1),
                'expectedResults' => array($expected[1], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('targetType' => 'varchar'),
                'orderBy' => array('createdTime' => 'desc'),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('targetId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('targetIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('endTime' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('orgCode' => 'varchar'),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('likeOrgCode' => 'var'),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('copyId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testDeleteByTargetIdAndTargetType()
    {
        $announcement = $this->getDao()->create($this->getDefaultMockFields());
        $result = $this->getDao()->deleteByTargetIdAndTargetType(1, 'varchar');

        $this->assertEquals(1, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,    // 公告发布人ID
            'targetType' => 'varchar',    // 公告类型
            'url' => 'varchar',
            'startTime' => 1,
            'endTime' => 2,
            'targetId' => 1,    // 所属ID
            'content' => 'text',    // 公告内容
            'orgId' => 1,    // 组织机构ID
            'orgCode' => 'varchar',    // 组织机构内部编码
            'copyId' => 1,    // 复制的公告ID
        );
    }
}
