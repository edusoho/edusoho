<?php

namespace Tests\Unit\InformationCollect\Dao;

use Biz\BaseTestCase;
use Biz\InformationCollect\Dao\ResultDao;

class ResultDaoTest extends BaseTestCase
{
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
                    'submitter' => 2,
                    'eventId' => 1,
                    'createdTime' => time(),
                ],
                [
                    'formTitle' => 'test1',
                    'submitter' => 3,
                    'eventId' => 1,
                    'createdTime' => time(),
                ],
                [
                    'formTitle' => 'test2',
                    'submitter' => 2,
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
