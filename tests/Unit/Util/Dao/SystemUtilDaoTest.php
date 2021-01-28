<?php

namespace Tests\Unit\Util\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class SystemUtilDaoTest extends BaseDaoTestCase
{
    public function testDeclares()
    {
        $result = $this->getSystemUtilDao()->declares();

        $this->assertEmpty($result);
    }

    protected function getDefaultMockFields()
    {
        return [];
    }

    protected function getSystemUtilDao()
    {
        return $this->createDao('Util:SystemUtilDao');
    }
}
