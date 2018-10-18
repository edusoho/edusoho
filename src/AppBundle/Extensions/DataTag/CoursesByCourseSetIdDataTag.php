<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class CoursesByCourseSetIdDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取多个教学计划.
     *
     * 可传入的参数：
     *   courseSetId 必需 课程ID
     *   status  选 是否发布 (draft, published, closed)
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
        $conditions = ArrayToolkit::parts($arguments, array('courseSetId', 'status'));

        $set = $this->getCourseService()->searchCourses(
            $conditions,
            array('seq' => 'ASC', 'createdTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $set;
    }
}
