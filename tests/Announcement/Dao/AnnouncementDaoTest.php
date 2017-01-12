<?php

namespace Tests\Announcement\Dao;

use Tests\Base\BaseDaoTestCase;

class AnnouncementDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $factor = array();
        $factor[] = $this->mockDataObject(array('startTime' => 2, 'context' => 'char'));
        $factor[] = $this->mockDataObject(array('userId' => 2, 'endTime' => 2, 'orgCode' => 'char'));
        $factor[] = $this->mockDataObject(array('targetType' => 'int', 'targetId' => 2, 'copyId' => 2));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('startTime' => 1),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('targetType' => 'varchar'),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('targetId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('targetIds' => array(1, 2)),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('endTime' => 2),
                'expectedResults' => array($factor[0], $factor[2]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('orgCode' => 'varchar'),
                'expectedResults' => array($factor[0], $factor[2]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('likeOrgCode' => 'char'),
                'expectedResults' => $factor,
                'expectedCount' => 3
            ),
            array(
                'condition' => array('copyeId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($factor[0], $factor[2]),
                'expectedCount' => 2
            ),
        );
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
