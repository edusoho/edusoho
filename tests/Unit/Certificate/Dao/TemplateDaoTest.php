<?php

namespace Tests\Unit\Certificate\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TemplateDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['name' => 'testname']);
        $expected[] = $this->mockDataObject(['targetType' => 'classroom']);
        $expected[] = $this->mockDataObject(['dropped' => 1]);

        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['nameLike' => 'name'],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['targetType' => 'classroom'],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['dropped' => 1],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ],
        ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    protected function getDefaultMockfields()
    {
        return [
            'name' => 'test',
            'targetType' => 'course',
            'certificateName' => 'cname',
            'recipientContent' => '$name$（$username$）同学：',
            'certificateContent' => '由于你在$courseName$ 课程中优异学习表现，最终完成课程并通过最终考核，特此发次证明！',
        ];
    }
}
