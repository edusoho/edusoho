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
     * 
     * @param  array $arguments 参数
     * @return array 课程
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException("courseId参数缺失");
        }
    	return $this->getCoursService()->getCourse($arguments['courseId']);
    }

    protected function getCoursService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}