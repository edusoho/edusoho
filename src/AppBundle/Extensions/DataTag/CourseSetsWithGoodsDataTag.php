<?php

namespace AppBundle\Extensions\DataTag;

class CourseSetsWithGoodsDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $conditions = [
            'status' => 'published',
            'parentId' => 0,
        ];

        $conditions = $this->getCourseService()->appendReservationConditions($conditions);

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        } elseif (!empty($arguments['categoryCode'])) {
            $category = $this->getCategoryService()->getCategoryByCode($arguments['categoryCode']);
            $conditions['categoryId'] = empty($category) ? -1 : $category['id'];
        }
        $orderBy = 'createdTime';
        if (!empty($arguments['orderBy'])) {
            $orderBy = $arguments['orderBy'];
        }
        $courseSetsGoods = $this->getCourseSetService()->searchCourseSetAdoptProductWithGoods(
            $conditions,
            $orderBy,
            0,
            $arguments['count']
        );

        return $this->fillCourseSetTeachersAndCategoriesAttribute($courseSetsGoods);
    }
}
