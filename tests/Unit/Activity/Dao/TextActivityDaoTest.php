<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TextActivityDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $activity1 = $this->getDao()->create(array('createdUserId' => 1));
        $activity2 = $this->getDao()->create(array('createdUserId' => 1));
        $results = $this->getDao()->findByIds(array(1, 2));

        $this->assertEquals(2, $results[1]['id']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'finishTime' => 'time',
            'finishDetail' => 'test',
        );
    }
}
