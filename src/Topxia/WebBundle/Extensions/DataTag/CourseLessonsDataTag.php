<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

/**
 * @todo  count参数不是必须的
 */
class CourseLessonsDataTag extends CourseBaseDataTag implements DataTag  
{
    /**
     * 获取一个课程的课时列表
     *
     * 可传入的参数：
     * 
     *   courseId 必需 课程ID
     * 
     * @param  array $arguments 参数
     * @return array 课时列表
     */

    public function getData(array $arguments)
    {
        $this->checkCourseId($arguments);
    	$lessons = $this->getCourseService()->getCourseLessons($arguments['courseId']);

        return $this->getCoursesAndUsers($lessons);
    }
}
