<?php

namespace Tests\Unit\Goods\Mediator;

use Biz\BaseTestCase;

class ClassroomGoodsMediatorTest extends BaseTestCase
{
    public function testOnCreate()
    {
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
