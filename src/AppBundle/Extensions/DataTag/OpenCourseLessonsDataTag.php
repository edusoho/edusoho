<?php

namespace AppBundle\Extensions\DataTag;

class OpenCourseLessonsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取公开课推荐课程列表.
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   count    必需 课程数量，取值不超过10
     *
     * @param array $arguments 参数
     *
     * @return array 课程课时列表
     */
    public function getData(array $arguments)
    {
        $count = isset($arguments['count']) ? $arguments['count'] : PHP_INT_MAX;
        $lessons = $this->getOpenCourseService()->searchLessons(array(
            'courseId' => $arguments['courseId'],
            'status' => 'published',
        ), array('seq' => 'ASC'), 0, $count);

        return $lessons;
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
