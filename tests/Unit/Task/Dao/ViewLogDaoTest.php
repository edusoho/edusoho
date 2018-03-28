<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;
use AppBundle\Common\TimeMachine;

class ViewLogDaoTest extends BaseDaoTestCase
{
    public function testSearchGroupByTime()
    {
        $this->mockDataObject();
        $diffTime = 24*60*60;
        $results = $this->getDao()->searchGroupByTime(array('fileType' => 'video', 'fileStorage' => 'cloud', 'fileSource' => 'self'), time() - $diffTime, time() + $diffTime);
        $first = reset($results);

        $this->assertEquals(1, $first['count']);
    }

    public function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'courseSetId' => 1,
            'taskId' => 1,
            'fileId' => 1,
            'userId' => 1,
            'fileType' => 'video',
            'fileStorage' => 'cloud',
            'fileSource' => 'self',
        );
    }
}
