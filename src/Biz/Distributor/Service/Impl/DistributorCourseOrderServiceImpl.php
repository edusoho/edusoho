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
        $tokenInfo = array('valid' => false);
        try {
            $drpService = $this->getDrpService();
            if (!empty($drpService)) {
                $parsedInfo = $drpService->parseToken($token);
                $tokenInfo = array(
                    'type' => 'courseOrder',
                    'product_id' => $parsedInfo['data']['course_id'],
                    'valid' => true,
                );
            }
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error DistributorCourseOrderServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
    }

    public function generateMockedToken($params)
    {
        $data = array(
            'distribution_type' => 'courseOrder',
            'course_id' => $params['courseId'],
            'merchant_id' => '123',
            'agency_id' => '333',
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
