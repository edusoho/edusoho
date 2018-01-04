<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\SignUtil;
use QiQiuYun\SDK\Helper\MarketingHelper;

class DrpService extends BaseService
{
    private $loginPath = '/merchant/login';
    private $merchantStudents = '/merchant/students';
    private $studentOrders = '/merchant/orders';

    /**
     * 生成登陆的表单
     *
     * @param array $user 当前登陆的ES用户
     * @param array $site 网校信息
     *
     * @return string form表单
     */
    public function generateLoginForm($user, $site)
    {
        $jsonStr = SignUtil::serialize(['user' => $user, 'site' => $site]);
        $sign = SignUtil::sign($this->auth, $jsonStr);
        $action = $this->baseUri.$this->loginPath;

        return MarketingHelper::generateLoginForm($action, $user, $site, $sign);
    }

    /**
     *  解析token，返回token的组成部分
     *
     * @param string $token
     *
     * @return array 内容如下:
     *               - coupon_price 奖励优惠券金额
     *               - coupon_expiry_day 奖励优惠券的有效天数
     *               - time 链接生成时间
     *               - nonce 参与签名计算的随机字符串
     *
     * @throws DrpException 签名不通过
     */
    public function parseToken($token)
    {
        $data = explode(':', $token);

        if (7 !== count($data)) {
            throw new DrpException('非法请求:sign格式不合法');
        }

        list($merchantId, $agencyId, $couponPrice, $couponExpiryDay, $time, $nonce, $signature) = $data;

        $json = SignUtil::serialize(['merchant_id' => $merchantId, 'agency_id' => $agencyId, 'coupon_price' => $couponPrice, 'coupon_expiry_day' => $couponExpiryDay]);
        $signText = implode('\n', array($time, $nonce, $json));
        $sign = $this->auth->sign($signText);
        if ($sign != $signature) {
            throw new DrpException('非法请求:sign值不一致');
        }

        return array('couponPrice' => $couponPrice, 'couponExpiryDay' => $couponExpiryDay, 'time' => $time, 'nonce' => $nonce);
    }

    /**
     * 上报通过分销平台注册的用户
     *
     * @param $data, 数组,形如[{$user},...]
     * user 内容如下:
     *  * user_source_id: 用户的Id
     *  * nickname: 用户名的用户名
     *  * mobile: 用户的手机号
     *  * registered_time: 当前记录的创建时间（用户注册时间）
     *  * token: 用户注册时用的token
     */
    public function postStudents($students)
    {
        return $this->postData($this->merchantStudents, $students);
    }

    /**
     * 上报属于分销平台的用户的订单
     *
     * @param $data
     */
    public function postOrders(array $orders)
    {
        $orders = MarketingHelper::transformOrders($orders);

        return $this->postData($this->studentOrders, $orders);
    }

    private function postData($path, $data)
    {
        $jsonStr = SignUtil::serialize($data);
        $jsonStr = SignUtil::cut($jsonStr);
        $sign = SignUtil::sign($this->auth, $jsonStr);

        return $this->client->request(
            'POST',
            $this->baseUri.$path,
            array(
                'data' => $data,
                'sign' => $sign,
            )
        );
    }
}
