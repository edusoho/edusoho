<?php

namespace Tests\Activity\Dao;

use Tests\Base\BaseDaoTestCase;

class DownloadFileDaoTest extends BaseDaoTestCase
{
    public function testFindByDownloadActivityId()
    {
        $activity = array(
            // 'id' => 0,
            'downloadActivityId' => 1,
            'title' => 's',
            'link' => 's',
            'fileId' => 1,
            'fileSize' => 1,
            'type' => 'a',
            'indicate' => 'a',
            'summary' => 'a'
        );
        $expectedResult = $this->getDownloadFileDao()->create($activity);
        $result = $this->getDownloadFileDao()->findByDownloadActivityId(1);

        $this->assertArrayEquals($result[0], $expectedResult, array_keys($activity));
    }

    protected function getDownloadFileDao()
    {
        return $this->getBiz()->dao('Activity:DownloadFileDao');
    }
}
