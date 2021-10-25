<?php

namespace Tests\Unit\Marker\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class MarkerDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['mediaId' => 3]);
        $expected[] = $this->mockDataObject(['second' => 3]);

        $testConditions = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['mediaId' => 3],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['second' => 3],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ],
        ];

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['second' => 3]);
        $result = $this->getDao()->findByIds([1, 2]);
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return [
            'second' => 2,
            'mediaId' => 2,
        ];
    }
}
