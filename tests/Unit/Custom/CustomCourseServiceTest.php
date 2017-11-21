<?php

namespace Tests\Unit\Course;

use AppBundle\Common\ReflectionUtils;
use CustomBundle\Biz\Course\Dao\Impl\CourseDaoImpl;
use CustomBundle\Biz\Course\Service\Impl\CourseServiceImpl;
use CustomBundle\Biz\CustomTestCase;

class CustomCourseServiceTest extends CustomTestCase
{
    public function testOverwriteService()
    {
        $courseService = $this->createService('Course:CourseService');

        $this->assertInstanceOf(get_class(new CourseServiceImpl($this->getBiz())), $courseService);
    }

    public function testOverwriteDao()
    {
        $courseDao = $this->createService('Course:CourseDao');

        $this->assertInstanceOf(get_class(new CourseDaoImpl($this->getBiz())), ReflectionUtils::getProperty($courseDao, 'dao'));
    }
}
