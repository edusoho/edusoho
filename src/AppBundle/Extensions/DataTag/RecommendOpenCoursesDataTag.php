<?php

namespace AppBundle\Extensions\DataTag;

use Biz\OpenCourse\Service\OpenCourseRecommendedService;

class RecommendOpenCoursesDataTag extends BaseDataTag implements DataTag
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
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $marktingRecommends = $this->getOpenCourseRecommendService()->searchRecommends(
            ['openCourseId' => $arguments['courseId']],
            ['seq' => 'ASC'],
            0, $arguments['count']
        );

        return $this->getOpenCourseRecommendService()->recommendedGoodsSort($marktingRecommends);
    }

    /**
     * @return OpenCourseRecommendedService
     */
    protected function getOpenCourseRecommendService()
    {
        return $this->getServiceKernel()->getBiz()->service('OpenCourse:OpenCourseRecommendedService');
    }
}
