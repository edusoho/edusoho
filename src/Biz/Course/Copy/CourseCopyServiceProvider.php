<?php

namespace Biz\Course\Copy;

use Pimple\ServiceProviderInterface;
use Biz\Course\Copy\Impl\CourseCopy;
use Biz\Course\Copy\Impl\ClassroomCourseCopy;

class CourseCopyServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['course_copy'] = function ($biz) {
            return new CourseCopy($biz);
        };

        $biz['classroom_course_copy'] = function ($biz) {
            return new ClassroomCourseCopy($biz);
        };
    }
}
