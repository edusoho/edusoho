<?php

namespace AppBundle\Extensions\DataTag;

class CourseRankByHitDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取按点击数排行的课程.
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = array(
            'status' => 'published',
            'excludeTypes' => array('reservation'),
        );

        $courses = $this->getCourseService()->searchCourses($conditions, 'hitNum', 0, $arguments['count']);

        return $this->getCourseTeachersAndCategories($courses);
    }
}
