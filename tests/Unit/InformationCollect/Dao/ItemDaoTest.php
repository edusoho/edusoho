<?php

namespace Tests\Unit\InformationCollect\Dao;

use Biz\BaseTestCase;

class ItemDaoTest extends BaseTestCase
{
    public function testFindByEventId()
    {
        $this->getInformationCollectItemDao()->batchCreate([
            ['id' => 1, 'eventId' => 1, 'code' => 'name', 'labelName' => '姓名', 'seq' => 1, 'required' => 1],
            ['id' => 2, 'eventId' => 1, 'code' => 'gender', 'labelName' => '性别', 'seq' => 2, 'required' => 1],
            ['id' => 3, 'eventId' => 2, 'code' => 'gender', 'labelName' => '性别', 'seq' => 2, 'required' => 1],
        ]);

        $items = $this->getInformationCollectItemDao()->findByEventId(1);

        $this->assertEquals(2, count($items));
        $this->assertEquals($items[0]['seq'], 1);
        $this->assertEquals($items[1]['seq'], 2);
    }

    protected function getInformationCollectItemDao()
    {
        return $this->createDao('InformationCollect:ItemDao');
    }
}
