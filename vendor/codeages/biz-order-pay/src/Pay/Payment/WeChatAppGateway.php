<?php
/**
 * Created by PhpStorm.
 * User: ilham
 * Date: 4/19/18
 * Time: 11:41 AM
 */

namespace Codeages\Biz\Pay\Payment;

class WeChatAppGateway extends WechatGateway
{
    protected function getSetting()
    {
        $config = $this->biz['payment.platforms']['wechat_app'];
        return array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],
            'cert_path' => $config['cert_path'],
            'key_path' => $config['key_path'],
        );
    }
}