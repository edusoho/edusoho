<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class RecommendOpenCoursesDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取公开课推荐课程列表
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *   count    必需 课程数量，取值不超过10
     *
     * @param  array $arguments     参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $recommendCourses = $this->getOpenCourseRecommendService()->searchRecommends(array('openCourseId' => $arguments['courseId']), array('seq', 'ASC'), 0, $arguments['count']);

        if (count($recommendCourses) < $arguments['count']) {
            $courses = $this->getCourseService()->searchCourses(array(), 'createdTime', 0, ($arguments['count'] - count($recommendCourses)));

            $recommendCourses = array_merge($recommendCourses, $courses);
        }

        return $recommendCourses;
    }

    protected function getOpenCourseRecommendService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseRecommendedService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
