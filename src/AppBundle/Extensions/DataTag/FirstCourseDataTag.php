<?php

namespace AppBundle\Extensions\DataTag;

class FirstCourseDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取第一个创建的教学计划.
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

        $courses = $this->getCourseService()->searchCourses(
            array(
                'courseSetId' => $arguments['courseSetId'],
            ),
            array('createdTime' => 'ASC'),
            0,
            1
        );

        return array_shift($courses);
    }
}
