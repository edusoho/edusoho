<?php

namespace Tests\FaceInspection\Service;

use Codeages\Biz\ItemBank\FaceInspection\Dao\RecordDao;
use Codeages\Biz\ItemBank\FaceInspection\Service\FaceInspectionService;
use Tests\IntegrationTestCase;

class FaceInspectionServiceTest extends IntegrationTestCase
{
    public function testCreateRecord()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerSceneService', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $record = [
            'user_id' => 1,
            'answer_scene_id' => 1,
            'answer_record_id' => 1,
            'status' => 'test',
            'level' => 1,
            'duration' => '1000',
            'behavior' => 'no_face',
            'picture_path' => '/files/facein/test.png',
        ];

        $result = $this->getFaceInspectionService()->createRecord($record);

        $this->assertEquals(1, $result['user_id']);
    }

    public function testCountRecord()
    {
        $this->mockRecord();
        $result = $this->getFaceInspectionService()->countRecord([]);

        $this->assertEquals(1, $result);
    }

    public function testSearchRecord()
    {
        $this->mockRecord();
        $result = $this->getFaceInspectionService()->searchRecord([], [], 0, 10);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['user_id']);
        $this->assertEquals('未检测到正脸', $result[0]['msg']);
    }

    public function testMakeToken()
    {
        $result = $this->getFaceInspectionService()->makeToken(1, 'test', 'test');

        $this->assertEquals(213, strlen($result));
    }

    public function mockRecord($result = [])
    {
        $default = [
            'user_id' => 1,
            'answer_scene_id' => 1,
            'answer_record_id' => 1,
            'status' => 'test',
            'level' => 1,
            'duration' => '1000',
            'behavior' => 'no_face',
            'picture_path' => '/files/facein/test.png',
        ];
        $result = array_merge($default, $result);

        return $this->getRecordDao()->create($result);
    }

    /**
     * @return RecordDao
     */
    protected function getRecordDao()
    {
        return $this->biz->dao('ItemBank:FaceInspection:RecordDao');
    }

    /**
     * @return FaceInspectionService
     */
    protected function getFaceInspectionService()
    {
        return $this->biz->service('ItemBank:FaceInspection:FaceInspectionService');
    }
}
