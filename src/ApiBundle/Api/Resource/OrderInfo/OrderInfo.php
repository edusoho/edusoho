<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Resource\Resource;

class OrderInfo extends Resource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId']) || empty($params['targetType'])) {
            throw new InvalidArgumentException("缺少参数");
        }

        $this->addVipParams($params);

        list($checkInfo, $orderInfo) = $this->service('Order:OrderFacadeService')->getOrderInfo($params['targetType'], $params['targetId'], $params);

        if (isset($checkInfo['error'])) {
            throw new InvalidArgumentException($checkInfo['error']);
        }

        $this->addOrderAssocInfo($orderInfo);


        return $orderInfo;
    }

    private function addVipParams(&$params)
    {
        if ($params['targetType'] == 'vip') {
            $vipSetting = $this->service('System:SettingService')->get('vip');
            $defaultUnitType = 'month';
            $defaultDuration = 3;
            if ($vipSetting) {
                if ($vipSetting['buyType'] == '10') {
                    $defaultDuration = $vipSetting['default_buy_months10'];
                    //按年
                } elseif ($vipSetting['buyType'] == '20') {
                    $defaultUnitType = 'year';
                    $defaultDuration = $vipSetting['default_buy_years'];
                    //按月
                } else {
                    $defaultDuration = $vipSetting['default_buy_months'];
                }
            }

            $params['unit'] = $defaultUnitType;
            $params['duration'] = $defaultDuration;
        }
    }

    private function addOrderAssocInfo(&$orderInfo)
    {
        $orderInfo['availableCoupons'] = $this->service('Card:CardService')->findCurrentUserAvailableCouponForTargetTypeAndTargetId(
            $orderInfo['targetType'], $orderInfo['targetId']
        );

        $coinSetting = $this->service('System:SettingService')->get('coin');

        if (!empty($coinSetting['coin_name'])) {
            $orderInfo['coinName'] = $coinSetting['coin_name'];
        } else {
            $orderInfo['coinName'] = '虚拟币';
        }
    }
}