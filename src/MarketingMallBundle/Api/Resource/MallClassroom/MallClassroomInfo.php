<?php


namespace MarketingMallBundle\Api\Resource\MallClassroom;


use ApiBundle\Api\ApiRequest;
use MarketingMallBundle\Api\Resource\BaseResource;
use MarketingMallBundle\Common\GoodsContentBuilder\ClassroomInfoBuilder;

class MallClassroomInfo extends BaseResource
{
    public function get(ApiRequest $request)
    {
        $courseId = $request->query->get('targetId');

        $builder = $this->getClassroomInfoBuilder();

        return $builder->build($courseId);
    }

    /**
     * @return ClassroomInfoBuilder
     */
    protected function getClassroomInfoBuilder()
    {
        return new ClassroomInfoBuilder($this->biz);
    }

}