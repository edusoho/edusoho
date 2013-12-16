<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseDataTag extends BaseDataTag implements DataTag  
{
    /**
     * 获取一个课程
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
    	$course = $this->getCourseService()->getCourse($arguments['courseId']);

        $course['teachers'] = $this->getTeachers($course);

        return $course;
    }

    protected function getTeachers($course)
    {
        return $this->getUserService()->findUsersByIds($course['teachers']);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}

