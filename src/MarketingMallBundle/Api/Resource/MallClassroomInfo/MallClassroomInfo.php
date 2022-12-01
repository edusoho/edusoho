<?php


namespace MarketingMallBundle\Api\Resource\MallClassroomInfo;


use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Common\GoodsContentBuilder\ClassroomInfoBuilder;
use MarketingMallBundle\Common\GoodsContentBuilder\CourseInfoBuilder;

class MallClassroomInfo extends BaseResource
{
    public function get(ApiRequest $request,$id)
    {
        $builder = $this->getClassroomInfoBuilder();

        return $builder->build($id);
    }

    /**
     * @return ClassroomInfoBuilder
     */
    protected function getClassroomInfoBuilder()
    {
        return new ClassroomInfoBuilder($this->biz);
    }
}