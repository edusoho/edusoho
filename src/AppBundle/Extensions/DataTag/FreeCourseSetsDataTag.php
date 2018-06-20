<?php

namespace AppBundle\Extensions\DataTag;

class FreeCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新课程列表.
     *
     * 可传入的参数：
     *   categoryId/categoryCode 可选 分类ID/分类编码
     *   orderby 可选 课程排序方式
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
            'parentId' => 0,
            'minCoursePrice' => 0,
            'excludeTypes' => array('reservation'),
        );

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        } elseif (!empty($arguments['categoryCode'])) {
            $category = $this->getCategoryService()->getCategoryByCode($arguments['categoryCode']);
            $conditions['categoryId'] = empty($category) ? -1 : $category['id'];
        }

        if (!empty($arguments['orderby'])) {
            $orderby = $arguments['orderby'];
        } else {
            $orderby = 'latest';
        }

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            $orderby,
            0,
            $arguments['count']
        );

        return $this->fillCourseSetTeachersAndCategoriesAttribute($courseSets);
    }
}
