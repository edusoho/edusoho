<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class CoursesCountDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取系统课程总数
     *     @return string 课程总数
     */

    public function getData(array $arguments)
    {
        $count = $this->getCourseService()->searchCourseCount(array());

        return $count;
    }

}
