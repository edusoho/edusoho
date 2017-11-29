<?php

namespace Tests\Unit\Course\Copy;

use Biz\BaseTestCase;

class CourseMemberCopyTest extends BaseTestCase
{
    public function testPreCopy()
    {

    }

    protected function mockCopyCourses()
    {
    
    }

    protected function mockCourse($title, $courseSet)
    {
        return array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
        );
    }
}