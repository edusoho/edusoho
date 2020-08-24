<?php


namespace Tests\Unit\Certificate;


use Biz\BaseTestCase;
use Biz\Certificate\Dao\RecordDao;
use Biz\Certificate\Service\RecordService;

class RecordServiceTest extends BaseTestCase
{
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

    private function createRecord()
    {
        return $this->getRecordDao()->create(
            [
                'id' => 1,
                'userId' => 1,
                'certificateId' => 1,
                'certificateCode' => '1234567890',
                'targetType' => 'course',
                'targetId' => 1,
                'status' => 'valid',
            ]
        );
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