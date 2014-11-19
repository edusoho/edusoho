<?php
namespace Custom\Service\Course\Impl;

use Custom\Service\Course\CourseOrder1Service;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\Impl\CourseOrderServiceImpl;

class CourseOrder1ServiceImpl extends CourseOrderServiceImpl implements CourseOrder1Service
{   
    protected function getOrderService()
    {
        return $this->createService('Custom:Order.Order1Service');
    }
}