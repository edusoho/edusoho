<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseHasFavoriteDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取一个课程
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     * 
     * @param  array $arguments 参数
     * @return  用户是否已经收藏该课程
     */
    
    public function getData(array $arguments)
    {   
        $this->checkCourseId($arguments);

    	return  $this->getCourseService()->hasFavoritedCourse($arguments['courseId']);
   
    }

    
}

