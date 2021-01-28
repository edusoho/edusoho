<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class CourseConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $course = ServiceKernel::instance()->createService('Course:CourseService')->getCourse($id);
        if (empty($course)) {
            throw new \Exception('course not found');
        }
        return $course;
    }

}

