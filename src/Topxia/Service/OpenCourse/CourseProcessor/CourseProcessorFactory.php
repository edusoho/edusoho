<?php
namespace Topxia\Service\OpenCourse\CourseProcessor;

use Topxia\Service\Common\ServiceKernel;

class CourseProcessorFactory
{
    public static function create($type)
    {
        if (!in_array($type, array('normal', 'live', 'open', 'liveOpen', 'course', 'openCourse'))) {
            throw new \Exception("课程类型不存在");
        }

        if (in_array($type, array('normal', 'live', 'course'))) {
            return ServiceKernel::instance()->createService('Course.CourseService');
        } elseif (in_array($type, array('open', 'liveOpen', 'openCourse'))) {
            return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
        }
    }
}
