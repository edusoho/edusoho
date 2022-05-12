<?php


namespace MarketingMallBundle\Api\Resource\MallCourse;


use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Common\CourseDetailBuilder;

class MallCourseInfo extends BaseResource
{
    public function get(ApiRequest $request)
    {
        $courseId = $request->query->get('targetId');

        $builder = new CourseDetailBuilder($this->biz);

        return $builder->build($courseId);
    }
}