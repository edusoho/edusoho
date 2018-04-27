<?php

namespace Biz\Distributor\Service\Impl;

use Biz\Distributor\Service\DistributorProductService;
use Biz\Distributor\Util\DistributorUtil;

class DistributorCourseOrderServiceImpl extends DistributorOrderServiceImpl implements DistributorProductService
{
    public function getSendType($data)
    {
        return 'order.'.$data['status'];
    }

    public function getRoutingName()
    {
        return 'course_show';
    }

    public function getRoutingParams($token)
    {
        return array('id' => DistributorUtil::getProductIdByToken($token));
    }

    /**
     * @param token 分销平台的token，只能使用一次
     *
     * @return array(
     *                'type' => 'courseOrder',
     *                'product_id' => '9', //商品id
     *                'valid' => true, //签名是否有效
     *                )
     */
    public function decodeToken($token)
    {
        try {
            $parsedInfo = $this->getDrpService()->parseCourseActivityToken($token);
            $splitedStr = explode(':', $token);
            $tokenInfo = array(
                'type' => 'courseOrder',
                'product_id' => $splitedStr[1],
                'valid' => true,
            );
        } catch (\Exception $e) {
            $tokenInfo = array('valid' => false);
            $this->biz['logger']->error('distributor sign error DistributorCourseOrderServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
    }

    public function generateMockedToken($params)
    {
        $data = array(
            'type' => 'courseOrder',
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

        $items = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $orderItem = $items[0];
        $user = $this->getUserService()->getUser($order['user_id']);

        $result['token'] = $orderItem['create_extra']['distributorToken'];
        $result['nickname'] = $user['nickname'];
        $result['mobile'] = $user['verifiedMobile'];

        if ('refunded' == $orderItem['status']) {
            $refund = $this->getOrderRefundService()->getOrderRefundById($orderItem['refund_id']);
            $result['refundedReason'] = $refund['reason'];
        }

        return $result;
    }

    protected function getJobType()
    {
        return 'CourseOrder';
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getOrderRefundService()
    {
        return $this->createService('Order:OrderRefundService');
    }
}
