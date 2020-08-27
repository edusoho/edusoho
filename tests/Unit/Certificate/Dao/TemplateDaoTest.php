<?php

namespace Tests\Unit\Certificate\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TemplateDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('name' => 'testname'));
        $expected[] = $this->mockDataObject(array('targetType' => 'classroom'));
        $expected[] = $this->mockDataObject(array('dropped' => 1));

        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('nameLike' => 'name'),
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('targetType' => 'classroom'),
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('dropped' => 1),
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ),
        );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    protected function getDefaultMockfields()
    {
        return array(
            'name' => 'test',
            'targetType' => 'course',
            'certificateName' => 'cname',
            'recipientContent' => '$name$（$username$）同学：',
            'certificateContent' => '由于你在$courseName$ 课程中优异学习表现，最终完成课程并通过最终考核，特此发次证明！',
        );
    }
}