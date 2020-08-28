<?php

namespace Tests\Unit\Certificate\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class RecordDaoTest extends BaseDaoTestCase
{
    public function testFindByCertificateId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['certificateId' => 2, 'certificateCode' => 'test222']);

        $res = $this->getDao()->findByCertificateId(2);

        $this->assertEquals('test222', $res[0]['certificateCode']);
    }

    public function testFindExpiredRecords()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['certificateId' => 2, 'status' => 'expired', 'certificateCode' => 'test222']);

        $res = $this->getDao()->findExpiredRecords(2);

        $this->assertEquals('test222', $res[0]['certificateCode']);
    }

    public function testFindByUserIdsAndCertificateId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['certificateId' => 2, 'userId' => 1, 'certificateCode' => 'test222']);

        $res = $this->getDao()->findByUserIdsAndCertificateId([1], 2);

        $this->assertEquals('test222', $res[0]['certificateCode']);
    }

    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['certificateId' => 2]);
        $expected[] = $this->mockDataObject(['certificateCode' => 'test222']);
        $expected[] = $this->mockDataObject(['userId' => 2]);
        $expected[] = $this->mockDataObject(['targetId' => 2]);
        $expected[] = $this->mockDataObject(['targetType' => 'classroom']);
        $expected[] = $this->mockDataObject(['issueTime' => 10000]);
        $expected[] = $this->mockDataObject(['expiryTime' => 9999]);
        $expected[] = $this->mockDataObject(['status' => 'valid']);

        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 8,
            ],
            [
                'condition' => ['certificateId' => 2],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['certificateIds' => [2]],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['certificateCode' => 'test222'],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['status' => 'valid'],
                'expectedResults' => [$expected[7]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['userId' => 2],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['expiryTime_NE' => 0],
                'expectedResults' => [$expected[6]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['issueTimeEgt' => 100],
                'expectedResults' => [$expected[5]],
                'expectedCount' => 1,
            ],
        ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    protected function getDefaultMockfields()
    {
        return [
            'userId' => 1,
            'certificateId' => 1,
            'certificateCode' => 'testCode111',
            'targetType' => 'course',
            'targetId' => 1,
        ];
    }
}
