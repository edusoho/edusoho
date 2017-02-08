<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class CourseLessonDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程的课时
     *
     * 可传入的参数：
     *
     *   lessonId 必需 课时ID
     *
     * @param  array $arguments 参数
     * @return array 课时
     */

    public function getData(array $arguments)
    {
        $this->checkLessonId($arguments);
        return $this->getCourseService()->getLesson($arguments['lessonId']);
    }
}
