<?php

namespace AppBundle\Extensions\DataTag;

class CourseSetByCourseDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个课程.
     *
     * 可传入的参数：
     *   courseId 必需 教学计划ID
     *
     * @param array $arguments 参数
     *
     * @return array 课程
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['courseId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('courseId参数缺失'));
        }
        $course = $this->getCourseService()->getCourse($arguments['courseId']);

        if (empty($course)) {
            return array();
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        if (empty($courseSet)) {
            return array();
        }

        if ($courseSet['categoryId'] != '0') {
            $courseSet['category'] = $this->getCategoryService()->getCategory($courseSet['categoryId']);
        }

        return $courseSet;
    }
}
