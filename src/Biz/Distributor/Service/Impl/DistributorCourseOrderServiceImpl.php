<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorProductService;
use Biz\Distributor\Util\DistributorUtil;

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

    public function getRoutingParams($token)
    {
        return array('id' => DistributorUtil::getProductIdByToken($token));
    }

    //TODO 分销平台接口弄好后 再根据接口改动
    public function decodeToken($token)
    {
        try {
            $splitedStr = explode(':', $token);
            $tokenInfo = array(
                'type' => $splitedStr[0],
                'product_id' => $splitedStr[1],
                'org_id' => $splitedStr[0],
                'merchant_id' => $splitedStr[3],
                'time' => $splitedStr[4],
                'once' => $splitedStr[5],
                'sign' => $splitedStr[6],
            );
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error DistributorCourseOrderServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
    }

    public function generateMockedToken($params)
    {
        $data = array(
            'type' => $this->getSendType(),
            'course_id' => $params['courseId'],
            'org_id' => '333',
            'merchant_id' => '123',
        );
        $tokenExpireDateNum = null;

        return $this->encodeToken($data, $tokenExpireDateNum);
    }

    protected function convertData($order)
    {
        $result = parent::convertData($order);

        return $result;
    }

    protected function getJobType()
    {
        return 'CourseOrder';
    }
}
