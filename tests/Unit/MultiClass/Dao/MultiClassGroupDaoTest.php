<?php

namespace Tests\Unit\MultiClass\Dao;

use Biz\BaseTestCase;
use Biz\MultiClass\Dao\MultiClassGroupDao;

class MultiClassGroupDaoTest extends BaseTestCase
{
    public function testFindGroupsByMultiClassId()
    {

    }

    /**
     * @return MultiClassGroupDao
     */
    protected function getMultiClassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }
}
