<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseThreadDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取一个课程话题
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   threadId 必需 课程话题ID
     * 
     * @param  array $arguments 参数
     * @return array 课程话题
     */

    public function getData(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException("courseId参数缺失");
        }

        if (empty($arguments['threadId'])) {
            throw new \InvalidArgumentException("threadId参数缺失");
        }

    	return $this->getThreadService()->getThread($arguments['courseId'], $arguments['threadId']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

}


?>