<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseDataTag extends CourseBaseDataTag implements DataTag  
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
        $this->checkCourseId($arguments);

    	$course = $this->getCourseService()->getCourse($arguments['courseId']);
        $course['teachers'] = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

        if ($course['categoryId'] != '0') {
            $course['category'] = $this->getCategoryService()->getCategory($course['categoryId']);
        }

        return $course;
    }

    
}

