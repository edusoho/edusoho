<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ViewLogDaoTest extends BaseDaoTestCase
{
    public function testSearchGroupByTime()
    {
        $this->mockDataObject();
        $results = $this->getDao()->searchGroupByTime(array('fileType' => 'video', 'fileStorage' => 'cloud', 'fileSource' => 'self'), time() - 1000, time() + 1000);
        $first = reset($results);

        $this->assertEquals(1, $first['count']);
        $this->assertEquals(date('Y-m-d'), $first['date']);
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
