<?php

namespace Tests\Unit\InformationCollect\Dao;

use Biz\BaseTestCase;
use Biz\InformationCollect\Dao\ResultItemDao;

class ResultItemDaoTest extends BaseTestCase
{
    public function testFindByResultId()
    {
        $this->mockResultItems();

        $resultItems = $this->getInformationCollectResultItemDao()->findByResultId(1);

        $this->assertEquals(2, count($resultItems));
    }

    public function findResultDataByResultIds()
    {
        $this->mockResultItems();

        $result = $this->getInformationCollectResultItemDao()->findResultDataByResultIds([1, 2]);

        $this->assertEquals(3, count($result));
    }

    protected function mockResultItems()
    {
        return $this->getInformationCollectResultItemDao()->batchCreate([
            ['id' => 1, 'eventId' => 1, 'resultId' => 1, 'code' => 'name', 'labelName' => '姓名', 'value' => '车凌锋'],
            ['id' => 2, 'eventId' => 1, 'resultId' => 1, 'code' => 'gender', 'labelName' => '性别', 'value' => '男'],
            ['id' => 3, 'eventId' => 1, 'resultId' => 2, 'code' => 'gender', 'labelName' => '性别', 'value' => '男'],
        ]);
    }

    /**
     * @return ResultItemDao
     */
    protected function getInformationCollectResultItemDao()
    {
        return $this->createDao('InformationCollect:ResultItemDao');
    }
}
