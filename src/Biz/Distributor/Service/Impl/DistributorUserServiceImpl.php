<?php

namespace Biz\Distributor\Service\Impl;

use QiQiuYun\SDK\Auth;
use AppBundle\Common\Exception\RuntimeException;
use AppBundle\Common\TimeMachine;

class DistributorUserServiceImpl extends BaseDistributorServiceImpl
{
    /**
     * 分销平台的token编码方式
     *   注意，$data 内的参数值必须为字符串
     *
     * @param $data, key顺序不能错误
     * array(
     *   'merchant_id' => '123',
     *   'agency_id' => '222',
     *   'coupon_price' => '222',  // 单位为分
     *   'coupon_expiry_day' => '12', //单位为天
     * )
     * @param $tokenExpireDateNum unix_time, 如果填了，则使用填写的时间，不填，则使用当前时间
     *
     * @return {merchant_id}:{agency_id}:{coupon_price}:{coupon_expiry_day}:{time}:{nonce}:{sign}
     *                                                                                            sign 为 添加 secretKey 后的加密方法
     */
    public function encodeToken($data, $tokenExpireDateNum = null)
    {
        if (empty($tokenExpireDateNum)) {
            $time = TimeMachine::time().'';
        } else {
            $time = strtotime('-1 day', $tokenExpireDateNum);
        }

        $once = md5(TimeMachine::time());

        $resultStr = '';
        foreach ($data as $key => $value) {
            if (!empty($resultStr)) {
                $resultStr .= ':';
            }

            $resultStr .= $value;
        }

        $resultStr .= ":{$time}:{$once}:{$this->sign($data, $time, $once)}";

        return $resultStr;
    }

    /**
     * 分销平台的token，只能使用一次
     *
     * @return array(
     *                'couponPrice' => 123, //优惠券，奖励多少分 （单位为分）
     *                'couponExpiryDay' => unix_time, //优惠券有效时间
     *                'registable'  => true, //是否可注册，指的是分销平台是否颁发过这个token， 如果为false，则注册的用户不算分销平台用户
     *                'rewardable' => false  //是否有奖励, 当couponPrice或couponExpiryday=0时, 则注册的用户不会发放优惠券
     *                )
     */
    public function decodeToken($token)
    {
        $splitedStr = explode(':', $token);

        $tokenInfo = array(
            'registable' => false,
            'rewardable' => false,
        );

        try {
            $drpService = $this->getDrpService();
            if (!empty($drpService)) {
                $this->validateExistedToken($token);
                $parsedInfo = $this->getDrpService()->parseToken($token);
                $tokenInfo['registable'] = true;
                $tokenExpireTime = strtotime('+1 day', intval($parsedInfo['time']));
                if ($tokenExpireTime >= TimeMachine::time()) {
                    $tokenInfo['couponPrice'] = $parsedInfo['couponPrice'];
                    $tokenInfo['couponExpiryDay'] = $parsedInfo['couponExpiryDay'];
                    if (0 != $tokenInfo['couponPrice'] && 0 != $tokenInfo['couponExpiryDay']) {
                        $tokenInfo['rewardable'] = true;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->biz['logger']->error('distributor sign error BaseDistributorServiceImpl::decodeToken '.$e->getMessage(), array('trace' => $e->getTraceAsString()));
        }

        return $tokenInfo;
    }

    public function getSendType()
    {
        return 'user';
    }

    protected function convertData($user)
    {
        return array(
           'user_source_id' => $user['id'],
           'nickname' => $user['nickname'],
           'mobile' => $user['verifiedMobile'],
           'registered_time' => $user['createdTime'],
           'token' => $user['token'],
        );
    }

    protected function getJobType()
    {
        return 'User';
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    private function sign($arr, $time, $once)
    {
        ksort($arr);
        $json = implode('\n', array($time, $once, json_encode($arr)));

        $settings = $this->getSettingService()->get('storage', array());
        $auth = new Auth($settings['cloud_access_key'], $settings['cloud_secret_key']);

        return $auth->sign($json);
    }

    private function validateExistedToken($token)
    {
        $existedUser = $this->getUserService()->searchUsers(
            array('distributorToken' => $token),
            array('id' => 'ASC'),
            0,
            1
        );
        if (!empty($existedUser)) {
            throw new RuntimeException('token already existed');
        }
    }
}
