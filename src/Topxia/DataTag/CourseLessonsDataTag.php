<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseLessonsDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取一个课程的课时
     *
     * 可传入的参数：
     * 
     *   courseId 必需 课程ID
     * 
     * @param  array $arguments 参数
     * @return array 课时
     */

    public function getData(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException("courseId参数缺失");
        }
    	return $this->getCourseService()->getCourseLessons($arguments['courseId']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}


?>