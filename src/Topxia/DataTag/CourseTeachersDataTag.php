<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseTeachersDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取一个课程所有老师
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     * 
     * @param  array $arguments 参数
     * @return array 课程老师
     */

    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);
        $course = $this->getCourseService()->getCourse($arguments['courseId']);
    	if(empty($course)){
            return array();
        }
        $teachers=array();
        foreach ($course['teacherIds'] as $teacherId) {
            $user=$this->getUserService()->getUser($teacherId);
            $userProfile = $this->getUserService()->getUserProfile($teacherId);
           $teachers[]=array_merge($user,$userProfile);
            
        }
        return $teachers;
    }

}
