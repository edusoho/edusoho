<?php

namespace AppBundle\Extensions\DataTag;

class PopularCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最热门课程列表.
     *
     * 可传入的参数：
     *   categoryId 可选 分类ID
     *   type 必须:(热门课程类型)
     *          hitNum  按照点击数
     *          recommended  按照推荐时间
     *          Rating  按照评分
     *          studentNum  按照学生数目
     *          recommendedSeq  按照推荐序号
     *
     *   price  可选(是否搜索付费课程)
     *         付费课程：true
     *         免费课程：不填
     *
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = array('status' => 'published', 'parentId' => 0);
        $orderBy = 'hitNum';
        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        // @todo 应该有３种模式： 全部、免费、收费

        if (!empty($arguments['price'])) {
            $conditions['maxCoursePrice_GT'] = '0.00';
        }

        if (!empty($arguments['type'])) {
            $orderBy = $arguments['type'];
        }
        if (in_array($orderBy, array('recommended', 'recommendedSeq'))) {
            $conditions['recommended'] = 1;
        }

        $conditions = $this->getCourseService()->appendReservationConditions($conditions);

        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, $orderBy, 0, $arguments['count']);

        return $this->fillCourseSetTeachersAndCategoriesAttribute($courseSets);
    }
}
