<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class CourseLessonsDataTag extends CourseBaseDataTag implements DataTag  
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
        $this->checkCourseId($arguments);

    	$lesson = $this->getCourseService()->getCourseLessons($arguments['courseId']);
        $lesson['teachers'] = $this->getUserService()->getUser($lesson['0']['userId']);
        $lesson['teachers']['password'] = NULL;
        $lesson['teachers']['salt'] = NULL;

        return $lesson;
    }

}
