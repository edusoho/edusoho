<?php

namespace Tests\Unit\CloudData\Dao;

use Biz\BaseTestCase;

class CloudDataDaoTest extends BaseTestCase
{
    public function testDeclares()
    {
        $this->assertArrayEquals(
            array(
                'timestamps' => array('createdTime', 'updatedTime'),
                'serializes' => array(
                    'body' => 'json',
                ),
            ),
            $this->getCloudDataDao()->declares()
        );
    }

    protected function getCloudDataDao()
    {
        return $this->biz->dao('CloudData:CloudDataDao');
    }
}
