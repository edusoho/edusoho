<?php

namespace Tests\Unit\InformationCollect\Dao;

use Biz\BaseTestCase;

class ResultDaoTest extends BaseTestCase
{
    public function testGetByUserIdAndEventId()
    {
        $this->getInformationCollectResultDao()->create([
            'id' => 1,
            'formTitle' => '测试表单',
            'userId' => 1,
            'eventId' => 1,
        ]);

        $result = $this->getInformationCollectResultDao()->getByUserIdAndEventId(1, 1);
        $this->assertEquals(1, $result['id']);
    }

    protected function getInformationCollectResultDao()
    {
        return $this->createDao('InformationCollect:ResultDao');
    }
}
