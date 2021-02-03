<?php

namespace Tests\Unit\Classroom\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[0] = $this->mockDataObject(['status' => 'closed']);
        $expected[1] = $this->mockDataObject(['title' => 'b']);
        $expected[2] = $this->mockDataObject(['price' => 55.5]);
        $expected[3] = $this->mockDataObject(['private' => 1]);
        $expected[4] = $this->mockDataObject(['categoryId' => 2]);
        $expected[5] = $this->mockDataObject(['recommended' => 1]);
        $expected[6] = $this->mockDataObject(['showable' => 0]);
        $expected[7] = $this->mockDataObject(['buyable' => 0]);
        $expected[8] = $this->mockDataObject(['orgCode' => '0']);
        $expected[9] = $this->mockDataObject(['headTeacherId' => 1]);
        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 10,
                ],
            [
                'condition' => ['status' => 'closed'],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['title' => 'b'],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['titleLike' => 'b'],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['price' => 55.5],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['price_GT' => 50],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['private' => 1],
                'expectedResults' => [$expected[3]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['categoryId' => 2],
                'expectedResults' => [$expected[4]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['recommended' => 1],
                'expectedResults' => [$expected[5]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['showable' => 0],
                'expectedResults' => [$expected[6]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['buyable' => 0],
                'expectedResults' => [$expected[7]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['orgCode' => '0'],
                'expectedResults' => [$expected[8]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['headTeacherId' => 1],
                'expectedResults' => [$expected[9]],
                'expectedCount' => 1,
                ],
            ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByTitle()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByTitle('a');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByLikeTitle()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['title' => 'ahaha']);
        $expected[] = $this->mockDataObject(['title' => 'ayaya']);
        $res = $this->getDao()->findByLikeTitle('a');
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['title' => 'ahaha']);
        $expected[] = $this->mockDataObject(['title' => 'ahaha']);
        $res = $this->getDao()->findByIds([1, 2]);
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
        $this->assertArrayEquals($expected[1], $res[1], $this->getCompareKeys());
    }

    public function refreshHotSeq()
    {
        $expected = $this->mockDataObject(['title' => 'ahaha', 'hotSeq' => 10]);
        $this->assertEquals(10, $expected['hotSeq']);

        $this->getDao()->refreshHotSeq();

        $classroom = $this->getDao()->get($expected['id']);
        $this->assertEquals(0, $classroom['hotSeq']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'status' => 'draft',
            'title' => 'a',
            'price' => 1.1,
            'private' => 0,
            'categoryId' => 1,
            'recommended' => 0,
            'showable' => 1,
            'buyable' => 1,
            'orgCode' => '1',
            'headTeacherId' => 0,
            ];
    }
}
