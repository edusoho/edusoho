<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class TeacherCoursesDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取特定老师的课程列表
     *
     * 可传入的参数：
     *   userId   必需 老师ID
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */

    public function getData(array $arguments)
    {	
        $this->checkCount($arguments);
        $this->checkUserId($arguments);
        
        $conditions = array(
            'status' => 'published', 
            'userId' => $arguments['userId']
        );
        $courses = $this->getCourseService()->searchCourses($conditions,'latest', 0, $arguments['count']);

    	return $this->getCourseTeachersAndCategories($courses);
    }

}
