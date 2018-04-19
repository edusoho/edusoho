<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorProductService;

class DistributorCourseOrderServiceImpl extends DistributorOrderServiceImpl implements DistributorProductService
{
    public function getSendType()
    {
        return 'courseOrder';
    }

    public function getRoutingName()
    {
        return 'course_show';
    }

    public function getRoutingParams($id)
    {
        return array('courseId' => $id);
    }

    protected function convertData($order)
    {
        $result = parent::convertData($order);
    }

    protected function getJobType()
    {
        return 'CourseOrder';
    }
}
