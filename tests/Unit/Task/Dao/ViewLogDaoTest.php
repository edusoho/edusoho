<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;
use AppBundle\Common\TimeMachine;

class ViewLogDaoTest extends BaseDaoTestCase
{
    public function testSearchGroupByTime()
    {
        $this->mockDataObject();
        $createdTime = TimeMachine::time();
        $results = $this->getDao()->searchGroupByTime(array('fileType' => 'video', 'fileStorage' => 'cloud', 'fileSource' => 'self'), $createdTime - 1000, $createdTime + 1000);
        $first = reset($results);

        $this->assertEquals(1, $first['count']);
        $this->assertEquals(date('Y-m-d', $createdTime), $first['date']);
    }

    public function getDefaultMockFields()
    {
        $createdTime = 1522199274;

        return array(
            'courseId' => 1,
            'courseSetId' => 1,
            'taskId' => 1,
            'fileId' => 1,
            'userId' => 1,
            'fileType' => 'video',
            'fileStorage' => 'cloud',
            'fileSource' => 'self',
            'createdTime' => TimeMachine::setMockedTime($createdTime),
        );
    }
}
