<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class FlashActivityDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $activity1 = $this->getDao()->create();
        $activity2 = $this->getDao()->create();
        $results = $this->getDao()->findByIds(array(1, 2));

        $this->assertEquals(2, $results[1]['id']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'mediaId' => 1,
            'finishType' => 'click',
            'finishDetail' => 'test',
        );
    }
}
