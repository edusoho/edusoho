<?php

namespace AppBundle\Extensions\DataTag;

class DefaultCourseByCourseSetDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个教学计划.
     *
     * 可传入的参数：
     *   courseSetId 必需 课程ID
     *
     * @param array $arguments 参数
     *
     * @return array 计划
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['courseSetId'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('courseSetId参数缺失'));
        }

        $course = $this->getCourseService()->getDefaultCourseByCourseSetId($arguments['courseSetId']);

        return $course;
    }
}
