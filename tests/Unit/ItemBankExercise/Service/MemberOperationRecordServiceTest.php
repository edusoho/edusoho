<?php


namespace Tests\Unit\ItemBankExercise\Service;


use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\MemberOperationRecordDao;
use Biz\ItemBankExercise\Service\MemberOperationRecordService;

class MemberOperationRecordServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $res = $this->getMemberOperationRecordService()->create(
            [
                'title' => 'test1',
                'memberId' => 1,
                'exerciseId' => 1,
                'userId' => 1,
                'operatorId' => 2,
                'operateType' => 'join'
            ]
        );

        $this->assertEquals($res['title'], 'test1');
        $this->assertEquals($res['memberId'], 1);
        $this->assertEquals($res['exerciseId'], 1);
        $this->assertEquals($res['userId'], 1);
        $this->assertEquals($res['operatorId'], 2);
    }

    public function testCount()
    {
        $this->batchCreateRecords();

        $res = $this->getMemberOperationRecordService()->count(
            [
                'exerciseId' => 1,
                'memberType' => 'student',
                'operateType' => 'join'
            ]
        );

        $this->assertEquals(1, count($res));
    }

    public function testSearch()
    {
        $this->batchCreateRecords();

        $res = $this->getMemberOperationRecordService()->search(
            [
                'exerciseId' => 1,
                'operateType' => 'join',
            ],
            null,
            0,
            1
        );

        $this->assertEquals('1', count($res));
        $this->assertEquals('1', $res[0]['exerciseId']);
        $this->assertEquals('join', $res[0]['operateType']);
    }

    protected function batchCreateRecords()
    {
        return $this->getMemberOperationRecordDao()->batchCreate(
            [
                [
                    'title' => 'test1',
                    'memberId' => 1,
                    'memberType' => 'teacher',
                    'exerciseId' => 1,
                    'userId' => 1,
                    'operatorId' => 2,
                ],
                [
                    'title' => 'test2',
                    'memberId' => 2,
                    'memberType' => 'student',
                    'exerciseId' => 1,
                    'userId' => 2,
                    'operatorId' => 2,
                ],
                [
                    'title' => 'test3',
                    'memberId' => 3,
                    'memberType' => 'student',
                    'exerciseId' => 2,
                    'userId' => 3,
                    'operatorId' => 2,
                ]
            ]
        );
    }

    /**
     * @return MemberOperationRecordService
     */
    protected function getMemberOperationRecordService()
    {
        return $this->createService('ItemBankExercise:MemberOperationRecordService');
    }

    /**
     * @return MemberOperationRecordDao
     */
    protected function getMemberOperationRecordDao()
    {
        return $this->createDao('ItemBankExercise:MemberOperationRecordDao');
    }
}