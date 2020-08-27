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
        $expected = array();
        $expected[] = $this->mockDataObject(array('certificateId' => 2));
        $expected[] = $this->mockDataObject(array('certificateCode' => 'test222'));
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('targetId' => 2));
        $expected[] = $this->mockDataObject(array('targetType' => 'classroom'));
        $expected[] = $this->mockDataObject(array('issueTime' => 10000));
        $expected[] = $this->mockDataObject(array('expiryTime' => 9999));
        $expected[] = $this->mockDataObject(array('status' => 'valid'));

        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 8,
            ),
            array(
                'condition' => array('certificateId' => 2),
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('certificateIds' => [2]),
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('certificateCode' => 'test222'),
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('status' => 'valid'),
                'expectedResults' => [$expected[7]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('expiryTime_NE' => 0),
                'expectedResults' => [$expected[6]],
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('issueTimeEgt' => 100),
                'expectedResults' => [$expected[5]],
                'expectedCount' => 1,
            ),
        );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    protected function getDefaultMockfields()
    {
        return array(
            'userId' => 1,
            'certificateId' => 1,
            'certificateCode' => 'testCode111',
            'targetType' => 'course',
            'targetId' => 1,
        );
    }
}