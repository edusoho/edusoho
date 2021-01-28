<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class CourseThreadConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $thread = ServiceKernel::instance()->createService('Course:ThreadService')->getThread(1,$id);
        if (empty($thread)) {
            throw new \Exception('course-thread not found');
        }
        return $thread;
    }

}

