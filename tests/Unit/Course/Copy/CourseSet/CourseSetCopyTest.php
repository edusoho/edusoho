<?php

namespace Tests\Unit\Course\Copy\CourseSet;

use Biz\BaseTestCase;
use Biz\Course\Copy\CourseSet\CourseSetCopy;

class CourseSetCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {
        $copy = new CourseSetCopy($this->biz, array(
            'class' => 'Biz\Course\Copy\CourseSet\CourseSetCopy',
            'priority' => 100,
        ),false);

        $this->assertNull($copy->preCopy(array(), array()));
    }

    public function testDoCopy()
    {

    }
}