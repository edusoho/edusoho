<?php

namespace Tests\FaceInspection\Dao;

use Codeages\Biz\ItemBank\FaceInspection\Dao\RecordDao;
use Tests\IntegrationTestCase;

class RecordDaoTest extends IntegrationTestCase
{
    public function testSearch()
    {
        $this->mockRecord();
        $this->mockRecord(['user_id' => 2, 'answer_scene_id' => 2, 'answer_record_id' => 2]);

        $result = $this->getRecordDao()->search(['user_id' => 1], [], 0, 10);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['user_id']);

        $result = $this->getRecordDao()->search(['answer_scene_id' => 1], [], 0, 10);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['answer_scene_id']);

        $result = $this->getRecordDao()->search(['answer_record_id' => 1], [], 0, 10);
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['answer_record_id']);
    }

    public function mockRecord($record = [])
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
        $record = array_merge($default, $record);

        return $this->getRecordDao()->create($record);
    }

    /**
     * @return RecordDao
     */
    protected function getRecordDao()
    {
        return $this->biz->dao('ItemBank:FaceInspection:RecordDao');
    }
}
