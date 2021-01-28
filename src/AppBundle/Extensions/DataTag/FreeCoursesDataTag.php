<?php

namespace AppBundle\Extensions\DataTag;

class FreeCoursesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取免费课程课程列表.
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        if (empty($arguments['categoryId'])) {
            $conditions = array('status' => 'published', 'price' => '0.00', 'parentId' => 0, 'excludeTypes' => array('reservation'));
        } else {
            $conditions = array('status' => 'published', 'price' => '0.00', 'categoryId' => $arguments['categoryId'], 'parentId' => 0, 'excludeTypes' => array('reservation'));
        }

        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, $arguments['count']);

        return $this->getCourseTeachersAndCategories($courses);
    }
}
