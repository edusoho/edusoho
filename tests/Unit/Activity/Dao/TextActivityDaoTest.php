<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TextActivityDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $activity1 = $this->mockDataObject();
        $activity2 = $this->mockDataObject();
        $results = $this->getDao()->findByIds(array(1, 2));

        $this->assertEquals(2, $results[1]['id']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'finishType' => 'time',
            'finishDetail' => 'test',
            'createdUserId' => 1,
        );
    }
}
