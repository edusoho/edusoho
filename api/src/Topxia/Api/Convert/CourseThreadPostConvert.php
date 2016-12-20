<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class CourseThreadPostConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $post = ServiceKernel::instance()->createService('Course:ThreadService')->getPost(1,$id);
        if (empty($post)) {
            throw new \Exception('course-thread-post not found');
        }
        return $post;
    }

}

