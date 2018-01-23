<?php

namespace AppBundle\Extensions\DataTag;

class OpenCourseLessonDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个公开课的课时.
     *
     * 可传入的参数：
     *
     *   lessonId 必需 课时ID
     *
     * @param array $arguments 参数
     *
     * @return array 课时
     */
    public function getData(array $arguments)
    {
        return $this->getOpenCourseService()->getLesson($arguments['lessonId']);
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
