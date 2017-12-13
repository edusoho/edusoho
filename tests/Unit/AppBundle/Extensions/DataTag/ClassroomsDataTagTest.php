<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ClassroomsDataTag;

class ClassroomsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new ClassroomsDataTag();
        $this->assertTrue(true);
    }
}
