<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderInfo extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId']) || empty($params['targetType'])) {
            throw new BadRequestHttpException('缺少参数', null, ErrorCode::INVALID_ARGUMENT);
        }

        $this->addVipParams($params);

        list($checkInfo, $orderInfo) = $this->service('Order:OrderFacadeService')->getOrderInfo($params['targetType'], $params['targetId'], $params);

        if (isset($checkInfo['error'])) {
            throw new BadRequestHttpException($checkInfo['error'], null, ErrorCode::INVALID_ARGUMENT);
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
            if ($vipSetting && !empty($vipSetting['buyType'])) {
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
        $couponTargetId = $orderInfo['targetId'];
        if ($orderInfo['targetType'] == 'course') {
            $course = $this->getCourseService()->getCourse($orderInfo['targetId']);
            $couponTargetId = $course['courseSetId'];
        }
        $orderInfo['availableCoupons'] = $this->service('Card:CardService')->findCurrentUserAvailableCouponForTargetTypeAndTargetId(
            $orderInfo['targetType'], $couponTargetId
        );

        $coinSetting = $this->service('System:SettingService')->get('coin');

        if (!empty($coinSetting['coin_name'])) {
            $orderInfo['coinName'] = $coinSetting['coin_name'];
        } else {
            $orderInfo['coinName'] = '虚拟币';
        }
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}