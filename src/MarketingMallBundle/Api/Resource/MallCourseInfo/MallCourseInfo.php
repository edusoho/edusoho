<?php


namespace MarketingMallBundle\Api\Resource\MallCourseInfo;


use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Common\GoodsContentBuilder\CourseInfoBuilder;

class MallCourseInfo extends BaseResource
{
    public function get(ApiRequest $request,$courseId)
    {
        $builder = $this->getCourseInfoBuilder();

        return $builder->build($courseId);
    }

    /**
     * @return CourseInfoBuilder
     */
    protected function getCourseInfoBuilder()
    {
        return new CourseInfoBuilder($this->biz);
    }
}