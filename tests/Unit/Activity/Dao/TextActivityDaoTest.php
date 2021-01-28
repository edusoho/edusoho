<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TextActivityDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $activity1 = $this->mockDataObject();
        $activity2 = $this->mockDataObject();
        $results = $this->getDao()->findByIds(array($activity1['id'], $activity2['id']));

        $this->assertContains($activity1, $results);
        $this->assertContains($activity2, $results);
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
