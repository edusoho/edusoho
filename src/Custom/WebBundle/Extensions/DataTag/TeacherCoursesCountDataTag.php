<?php

namespace Custom\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;
use Topxia\WebBundle\Extensions\DataTag\CourseBaseDataTag;

class TeacherCoursesCountDataTag extends CourseBaseDataTag implements DataTag  
{

    /**
     * 获取特定老师的课程列表
     * @todo  逻辑有问题，应该是取老师所在的所有课程，而不是创建者创建的所有课程
     * 
     * 可传入的参数：
     *   userId   必需 老师ID
     * 
     * @param  array $arguments 参数
     * @return int 课程总数
     */

    public function getData(array $arguments)
    {	
        $this->checkUserId($arguments);
        
        $conditions = array(
            'userId' => $arguments['userId'],
            'parentId' => 0
        );

        $coursesCount = $this->getCourseService()->findUserTeachCourseCount($conditions);

    	return $coursesCount;
    }

}
