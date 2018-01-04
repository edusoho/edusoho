<?php

namespace Biz\Distributor\Service;

interface DistributorService
{
    public function findJobData();

    public function createJobData($data);

    /**
     * 分销平台的token编码方式
     *
     * @param $data
     * array(
     *   'merchant_id' => 123,
     *   'agency_id' => 222,
     *   'coupon_price' => 222,
     *   'coupon_expiry_day' => unix_time,
     * )
     * @param $time unix_time, 如果填了，则使用填写的时间，不填，则使用当前时间
     *
     * @return {merchant_id}:{agency_id}:{coupon_price}:{coupon_expiry_day}:{time}:{nonce}:{sign}
     *                                                                                            sign 为 添加 secretKey 后的加密方法
     */
    public function encodeToken($data, $time = null);

    public function decodeToken($token);

    public function getDrpService();
}
