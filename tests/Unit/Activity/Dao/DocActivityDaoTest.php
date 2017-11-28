<?php

namespace Tests\Unit\Activity\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class DocActivityDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $activity1 = $this->mockDataObject(array('mediaId' => 1));
        $activity2 = $this->mockDataObject(array('mediaId' => 2));
        $results = $this->getDao()->findByIds(array(1, 2));

        $this->assertEquals(2, $results[1]['mediaId']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'finishType' => 'click',
            'finishDetail' => 'test',
            'createdUserId' => 1,
        );
    }
}
