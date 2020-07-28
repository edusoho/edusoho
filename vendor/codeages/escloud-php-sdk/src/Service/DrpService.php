<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Helper\MarketingHelper;
use ESCloud\SDK\Exception\SDKException;

class DrpService extends BaseService
{
    protected $host = 'fx.qiqiuyun.net';

    private $loginPath = '/merchant/login';
    private $postDataPath = '/merchant_data/actions/report';

    protected $service = 'drp';

    /**
     * 生成登陆的表单
     *
     * @param array $user 当前登陆的ES用户
     *                    user信息如下：
     *                    * user_source_id 用户在ES的Id
     *                    * nickname 用户在ES的昵称
     *                    * avatar 用户头像
     * @param array $site 网校信息
     *                    site网校信息
     *                    * domain 网校网址
     *                    * name 网校名称
     *                    * logo 网校logo
     *                    * about 网校介绍
     *                    * wechat 网校微信客服
     *                    * qq 网校qq客服
     *                    * telephone 网校电话客服
     *
     * @return string form表单
     */
    public function generateLoginForm($user, $site)
    {
        $data = array('site' => $site, 'user' => $user);
        ksort($data);
        $signingText = json_encode($data);
        $signature = $this->auth->makeRequestAuthorization($this->loginPath, $signingText);
        $action = $this->getRequestUri($this->loginPath);

        return MarketingHelper::generateLoginForm($action, $user, $site, $signature);
    }

    /**
     *  解析用户注册时用到的token，返回token的组成部分
     *
     * @param string $token
     *
     * @return array 内容如下:
     *               - coupon_price 奖励优惠券金额
     *               - coupon_expiry_day 奖励优惠券的有效天数
     *               - time 链接生成时间
     *               - nonce 参与签名计算的随机字符串
     *
     * @throws SDKException 签名不通过
     */
    public function parseRegisterToken($token)
    {
        $token = explode(':', $token);
        if (7 !== count($token)) {
            throw new SDKException('非法请求:token格式不合法');
        }
        list($merchantId, $agencyId, $couponPrice, $couponExpiryDay, $time, $nonce, $expectSign) = $token;

        $data = array('merchant_id' => $merchantId, 'agency_id' => $agencyId, 'coupon_price' => $couponPrice, 'coupon_expiry_day' => $couponExpiryDay);
        ksort($data);
        $dataStr = json_encode($data);
        $signingText = implode("\n", array($nonce, $time, $dataStr));
        $actualSign = $this->auth->makeSignature($signingText);
        if ($expectSign != $actualSign) {
            throw new SDKException('非法请求:sign值不一致');
        }

        return array('coupon_price' => $couponPrice, 'coupon_expiry_day' => $couponExpiryDay, 'time' => $time, 'nonce' => $nonce);
    }

    /**
     *  解析课程分销时用到的token，返回token的组成部分
     *
     * @param string $token
     *
     * @return array 内容如下:
     *               - distribution_type 分销活动类型
     *               - course_id 课程id
     *               - time 链接生成时间
     *               - nonce 参与签名计算的随机字符串
     *               - data Array
     *               - course_id 课程id
     *
     * @throws SDKException 签名不通过
     */
    private function parseCourseOrderToken($token)
    {
        $token = explode(':', $token);
        if (7 !== count($token)) {
            throw new SDKException('非法请求:token格式不合法');
        }
        list($distributionType, $courseId, $merchantId, $agencyId, $time, $nonce, $expectSign) = $token;

        $data = array('distribution_type' => $distributionType, 'merchant_id' => $merchantId, 'agency_id' => $agencyId, 'course_id' => $courseId);
        ksort($data);
        $dataStr = json_encode($data);
        $signingText = implode("\n", array($nonce, $time, $dataStr));
        $actualSign = $this->auth->makeSignature($signingText);
        if ($expectSign != $actualSign) {
            throw new SDKException('非法请求:sign值不一致');
        }

        return array(
            'distribution_type' => $distributionType,
            'time' => $time,
            'nonce' => $nonce,
            'data' => array(
                'course_id' => $courseId,
            ),
        );
    }

    /**
     *  解析分销时用到的token，返回token的组成部分
     *
     * @param string $token
     *
     * @return array 内容如下:
     *               - distribution_type 分销活动类型
     *               - time 链接生成时间
     *               - nonce 参与签名计算的随机字符串
     *               - data Array类型，不同分销类型有不同的数据
     *
     * @throws SDKException 签名不通过
     */
    public function parseToken($token)
    {
        $explodedToken = explode(':', $token);
        list($distributionType) = $explodedToken;
        $parseMethod = 'parse'.ucfirst($distributionType).'Token';

        return $this->$parseMethod($token);
    }

    /**
     * 上报通过分销平台注册的用户,或者他们的订单信息
     *
     * @param string $type  数据类型，user，order
     * @param array  $data, 数组,形如[{$user},...]
     *                      user 内容如下:
     *                      * user_source_id: 用户的Id
     *                      * nickname: 用户名的用户名
     *                      * mobile: 用户的手机号
     *                      * registered_time: 当前记录的创建时间（用户注册时间）
     *                      * updated_time:用户信息修改时间
     *                      * token: 用户注册时用的token
     *                      order 内容如下：
     *                      * user_source_id 订单的用户Id
     *                      * source_id 订单的Id
     *                      * product_type 商品类型
     *                      * product_id 商品Id
     *                      * title  订单title
     *                      * sn 订单编号
     *                      * created_time 订单创建时间
     *                      * payment_time 支付时间
     *                      * refund_expiry_day 退款有效期（X天）
     *                      * refund_deadline 退款截止时间
     *                      * price 订单价格（分）
     *                      * pay_amount 订单支付金额（分）
     *                      * deduction [{'type'=>'adjust_price','detail'=>'修改价格','amount'=>1(分)},...]
     *                      * status 订单状态
     *
     * @return array success=true
     *
     * @throws DrpException 上报数据异常
     */
    public function postData($type, array $data)
    {
        if (empty($type) || empty($data)) {
            throw new SDKException("Required 'data' and 'type'");
        }
        $data = array('type' => $type, 'data' => $data);
        ksort($data);

        return $this->request('POST', $this->postDataPath, $data);
    }
}
