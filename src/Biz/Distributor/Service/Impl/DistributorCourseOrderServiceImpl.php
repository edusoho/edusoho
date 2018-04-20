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

    public function getRoutingParams(array $tokenInfo)
    {
        return array('id' => $tokenInfo['product_id']);
    }

    //TODO 分销平台接口弄好后 再根据接口改动
    public function decodeToken($token)
    {
        try {
            $splitedStr = explode(':', $token);
            $tokenInfo = array(
                'org_id' => $splitedStr[0],
                'type' => $splitedStr[1],
                'product_id' => $splitedStr[2],
                'merchant_id' => $splitedStr[3],
                'time' => $splitedStr[4],
                'once' => $splitedStr[5],
                'sign' => $splitedStr[6],
            );
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error BaseDistributorServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
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
