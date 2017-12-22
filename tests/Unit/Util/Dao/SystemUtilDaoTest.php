<?php

namespace Tests\Unit\Util\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class SystemUtilDaoTest extends BaseDaoTestCase
{
    public function testGetCourseIdsWhereCourseHasDeleted()
    {
        $result = $this->getSystemUtilDao()->getCourseIdsWhereCourseHasDeleted();

        $this->assertEmpty($result);
    }

    public function testDeclares()
    {
        $result = $this->getSystemUtilDao()->declares();

        $this->assertEmpty($result);
    }

    protected function getDefaultMockFields()
    {
        return array();
    }

    protected function getSystemUtilDao()
    {
        return $this->createDao('Util:SystemUtilDao');
    }
}
