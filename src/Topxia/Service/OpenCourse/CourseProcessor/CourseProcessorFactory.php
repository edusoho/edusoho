<?php
namespace Topxia\Service\OpenCourse\CourseProcessor;

use Topxia\Service\Common\ServiceKernel;

class CourseProcessorFactory
{

	public static function create($type)
    {
    	if(!in_array($type, array('normal','live','open','liveOpen'))) {
    		throw new Exception("课程类型不存在");
    	}

    	if (in_array($type, array('normal','live'))) {
    		return ServiceKernel::instance()->createService('Course.CourseService');
    	} else if (in_array($type, array('open','liveOpen'))) {
    		return ServiceKernel::instance()->createService('OpenCourse.OpenCourseService');
    	}

    }

}


