<?php

namespace Tests\Unit\InformationCollect\Dao;

use Biz\BaseTestCase;
use Biz\InformationCollect\Dao\ResultDao;

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

    public function testCountGroupByEventId()
    {
        $this->mockResults();
        $result = $this->getInformationCollectResultDao()->countGroupByEventId([1, 2]);

        $this->assertEquals([['eventId' => 1, 'collectNum' => 2], ['eventId' => 2, 'collectNum' => 1]], $result);
    }

    protected function mockResults()
    {
        $results = $this->getInformationCollectResultDao()->batchCreate(
            [
                [
                    'formTitle' => 'test1',
                    'userId' => 2,
                    'eventId' => 1,
                    'createdTime' => time(),
                ],
                [
                    'formTitle' => 'test1',
                    'userId' => 3,
                    'eventId' => 1,
                    'createdTime' => time(),
                ],
                [
                    'formTitle' => 'test2',
                    'userId' => 2,
                    'eventId' => 2,
                    'createdTime' => time(),
                ],
            ]
        );

        return $results;
    }

    /**
     * @return ResultDao
     */
    protected function getInformationCollectResultDao()
    {
        return $this->createDao('InformationCollect:ResultDao');
    }
}
