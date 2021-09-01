<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        for ($i = 0; $i < 10; ++$i) {
            $expected[] = $this->mockDataObject();
        }

        $testConditions = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['courseId' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['courseSetId' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['taskId' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['userId' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['type' => 'discussion'],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['types' => ['discussion', 'question']],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['isStick' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['isElite' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['postNum' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['postNumLargerThan' => 0],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['title' => '哼'],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['content' => '爱上地方'],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['courseIds', [1, 2]],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
            [
                'condition' => ['private' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 10,
            ],
        ];

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindThreadIds()
    {
        $res[0] = $this->mockDataObject();
        $res[1] = $this->mockDataObject();
        $res[2] = $this->mockDataObject(['userId' => 2]);

        $this->assertEquals(2, count($this->getDao()->findThreadIds(['userId' => 1])));
    }

    public function testFindLatestThreadsByType()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['createdTime' => 1000]);
        $result = $this->getDao()->findLatestThreadsByType('discussion', 0, 5);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindEliteThreadsByType()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['createdTime' => 1000]);
        $result = $this->getDao()->findEliteThreadsByType('discussion', 1, 0, 5);
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    public function testFindThreadsByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['createdTime' => 1000]);
        $result = $this->getDao()->findThreadsByCourseId(1, ['createdTime'], 0, 5);
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    public function testFindThreadsByCourseIdAndType()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['createdTime' => 1000]);
        $result = $this->getDao()->findThreadsByCourseIdAndType(1, 'discussion', ['createdTime'], 0, 5);
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    public function testCountThreadsGroupedByCourseId()
    {
        $this->mockDataObject(['courseId' => 1]);
        $this->mockDataObject(['courseId' => 1]);
        $this->mockDataObject(['courseId' => 2]);

        $result = $this->getDao()->countThreadsGroupedByCourseId(['courseIds' => [1]]);
        $this->assertEquals(2, $result[0]['count']);
    }

    protected function mockDataObject($fields = [])
    {
        return $this->getDao()->create(array_merge($this->getDefaultMockFields(), $fields));
    }

    protected function getDefaultMockFields()
    {
        return [
            'courseId' => 1,
            'taskId' => 1,
            'userId' => 1,
            'type' => 'discussion',
            'isStick' => 1,
            'isElite' => 1,
            'isClosed' => 1,
            'private' => 1,
            'title' => '嗯哼？',
            'content' => '爱上地方',
            'postNum' => 1,
            'hitNum' => 1,
            'followNum' => 1,
            'latestPostTime' => time(),
            'courseSetId' => 1,
        ];
    }
}
