<?php

namespace Tests\Unit\WrongBook\Dao;

use Biz\BaseTestCase;
use Biz\WrongBook\Dao\WrongQuestionCollectDao;

class WrongQuestionCollectDaoTest extends BaseTestCase
{
    public function testGetCollectBYPoolIdAndItemId()
    {
        $collect = $this->mockWrongQuestionCollect();
        $getCollect = $this->getWrongQuestionCollectDao()->getCollectBYPoolIdAndItemId($collect['pool_id'], $collect['item_id']);
        $this->assertEquals('2', $getCollect['item_id']);
    }

    public function testGetCollectBYPoolId()
    {
        $collect = $this->mockWrongQuestionCollect();
        $getCollect = $this->getWrongQuestionCollectDao()->findCollectBYPoolId($collect['pool_id']);
        $this->assertCount(1, $getCollect);
    }

    protected function mockWrongQuestionCollect()
    {
        $wrongQuestionCollect = [
            'pool_id' => 1,
            'item_id' => 2,
        ];

        return $this->getWrongQuestionCollectDao()->create($wrongQuestionCollect);
    }

    /**
     * @return WrongQuestionCollectDao
     */
    protected function getWrongQuestionCollectDao()
    {
        return $this->getBiz()->dao('WrongBook:WrongQuestionCollectDao');
    }
}
