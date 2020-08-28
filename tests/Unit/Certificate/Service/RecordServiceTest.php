<?php

namespace Tests\Unit\Certificate\Service;

use Biz\BaseTestCase;
use Biz\Certificate\Dao\RecordDao;
use Biz\Certificate\Service\RecordService;

class RecordServiceTest extends BaseTestCase
{
    public function testGet()
    {
        $record = $this->createRecord();
        $res = $this->getRecordService()->get($record['id']);

        $this->assertEquals('1234567890', $res['certificateCode']);
    }

    public function testCount()
    {
        $record = $this->createRecord();
        $res = $this->getRecordService()->count(['userId' => 1]);

        $this->assertEquals(1, $res);
    }

    public function testSearch()
    {
        $record = $this->createRecord();
        $res = $this->getRecordService()->search(['userId' => 1], [], 0, 10);

        $this->assertEquals('1234567890', $res[0]['certificateCode']);
    }

    public function testFindExpiredRecords()
    {
        $record = $this->createRecord(['status' => 'expired']);
        $res = $this->getRecordService()->findExpiredRecords(1);

        $this->assertEquals('1234567890', $res[0]['certificateCode']);
    }

    public function testFindRecordsByCertificateId()
    {
        $record = $this->createRecord();
        $res = $this->getRecordService()->findRecordsByCertificateId(1);

        $this->assertEquals('1234567890', $res[0]['certificateCode']);
    }

    public function testCancelRecord()
    {
        $record = $this->createRecord(['status' => 'valid']);
        $res = $this->getRecordService()->cancelRecord($record['id']);

        $this->assertEquals('cancelled', $res['status']);
    }

    public function testGrantRecord()
    {
        $record = $this->createRecord(['status' => 'cancelled']);
        $res = $this->getRecordService()->grantRecord($record['id'], ['issueTime' => time()]);

        $this->assertEquals('valid', $res['status']);
    }

    public function testIsObtained()
    {
        $this->createRecord();

        $res = $this->getRecordService()->isObtained(1, 1);

        $this->assertTrue($res);
    }

    public function testIsCertificatesObtained()
    {
        $this->batchCreateRecord();

        $res = $this->getRecordService()->isCertificatesObtained(1, [1, 2, 3]);

        $this->assertEquals([1 => true, 2 => true, 3 => false], $res);
    }

    public function testPassCertificateRecord()
    {
        $record = $this->createRecord(['status' => 'none']);
        $res = $this->getRecordService()->passCertificateRecord($record['id'], 1);

        $this->assertEquals('valid', $res['status']);
    }

    public function testRejectCertificateRecord()
    {
        $record = $this->createRecord(['status' => 'none']);
        $res = $this->getRecordService()->rejectCertificateRecord($record['id'], 1);

        $this->assertEquals('reject', $res['status']);
    }

    public function testResetCertificateRecord()
    {
        $record = $this->createRecord(['status' => 'reject']);
        $res = $this->getRecordService()->resetCertificateRecord($record['id']);

        $this->assertEquals('none', $res['status']);
    }

    public function testAutoIssueCertificates()
    {
        $res = $this->getRecordService()->autoIssueCertificates(1, []);
        $this->assertTrue($res);

        $this->mockBiz('Certificate:CertificateService', [
            [
                'functionName' => 'get',
                'returnValue' => [
                    'id' => 1, 'targetId' => 1, 'targetType' => 'course', 'autoIssue' => 1, 'expiryDay' => 1, 'code' => 'code', 'status' => 'published',
                ],
            ],
        ]);
        $this->getRecordService()->autoIssueCertificates(1, [1]);
        $records = $this->getRecordService()->findRecordsByCertificateId(1);
        $this->assertEquals(1, $records[0]['userId']);

        $this->getRecordService()->autoIssueCertificates(1, [1]);
        $records = $this->getRecordService()->findRecordsByCertificateId(1);
        $this->assertEquals(1, $records[0]['userId']);
        $this->assertEquals(1, count($records));
    }

    public function testCheckExpireCertificate()
    {
        $res = $this->getRecordService()->checkExpireCertificate();
        $this->assertEmpty($res);

        $record = $this->createRecord(['status' => 'valid', 'expiryTime' => 100000]);
        $this->getRecordService()->checkExpireCertificate();
        $res = $this->getRecordService()->get($record['id']);

        $this->assertEquals('expired', $res['status']);
    }

    private function createRecord($record = [])
    {
        $default = [
            'id' => 1,
            'userId' => 1,
            'certificateId' => 1,
            'certificateCode' => '1234567890',
            'targetType' => 'course',
            'targetId' => 1,
            'status' => 'valid',
        ];
        $record = array_merge($default, $record);

        return $this->getRecordDao()->create($record);
    }

    private function batchCreateRecord()
    {
        return $this->getRecordDao()->batchCreate([
            [
                'id' => 1,
                'userId' => 1,
                'certificateId' => 1,
                'certificateCode' => '1234567890',
                'targetType' => 'course',
                'targetId' => 1,
                'status' => 'valid',
            ],
            [
                'id' => 2,
                'userId' => 1,
                'certificateId' => 2,
                'certificateCode' => '1234567811',
                'targetType' => 'course',
                'targetId' => 1,
                'status' => 'expired',
            ],
            [
                'id' => 3,
                'userId' => 2,
                'certificateId' => 3,
                'certificateCode' => '1234527290',
                'targetType' => 'course',
                'targetId' => 1,
                'status' => 'valid',
            ],
        ]);
    }

    /**
     * @return RecordDao
     */
    private function getRecordDao()
    {
        return $this->createDao('Certificate:RecordDao');
    }

    /**
     * @return RecordService
     */
    private function getRecordService()
    {
        return $this->createService('Certificate:RecordService');
    }
}
