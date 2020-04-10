# Omnipay: WechatPay

**WechatPay driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/lokielse/omnipay-wechatpay.png?branch=master)](https://travis-ci.org/lokielse/omnipay-wechatpay)
[![Latest Stable Version](https://poser.pugx.org/lokielse/omnipay-wechatpay/version.png)](https://packagist.org/packages/lokielse/omnipay-wechatpay)
[![Total Downloads](https://poser.pugx.org/lokielse/omnipay-wechatpay/d/total.png)](https://packagist.org/packages/lokielse/omnipay-wechatpay)

[Omnipay](https://github.com/omnipay/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements WechatPay support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install:

    composer require lokielse/omnipay-wechatpay

## Basic Usage

The following gateways are provided by this package:


* WechatPay (Wechat Common Gateway) 微信支付通用网关
* WechatPay_App (Wechat App Gateway) 微信APP支付网关
* WechatPay_Native (Wechat Native Gateway) 微信原生扫码支付支付网关
* WechatPay_Js (Wechat Js API/MP Gateway) 微信网页、公众号、小程序支付网关
* WechatPay_Pos (Wechat Micro/POS Gateway) 微信刷卡支付网关
* WechatPay_Mweb (Wechat H5 Gateway) 微信H5支付网关

## Usage

### Create Order [doc](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1)

```php
//gateways: WechatPay_App, WechatPay_Native, WechatPay_Js, WechatPay_Pos, WechatPay_Mweb
$gateway    = Omnipay::create('WechatPay_App');
$gateway->setAppId($config['app_id']);
$gateway->setMchId($config['mch_id']);
$gateway->setApiKey($config['api_key']);

$order = [
    'body'              => 'The test order',
    'out_trade_no'      => date('YmdHis').mt_rand(1000, 9999),
    'total_fee'         => 1, //=0.01
    'spbill_create_ip'  => 'ip_address',
    'fee_type'          => 'CNY'
];

/**
 * @var Omnipay\WechatPay\Message\CreateOrderRequest $request
 * @var Omnipay\WechatPay\Message\CreateOrderResponse $response
 */
$request  = $gateway->purchase($order);
$response = $request->send();

//available methods
$response->isSuccessful();
$response->getData(); //For debug
$response->getAppOrderData(); //For WechatPay_App
$response->getJsOrderData(); //For WechatPay_Js
$response->getCodeUrl(); //For Native Trade Type
```

### Notify [doc](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_7&index=3)
```php
$gateway    = Omnipay::create('WechatPay');
$gateway->setAppId($config['app_id']);
$gateway->setMchId($config['mch_id']);
$gateway->setApiKey($config['api_key']);

$response = $gateway->completePurchase([
    'request_params' => file_get_contents('php://input')
])->send();

if ($response->isPaid()) {
    //pay success
    var_dump($response->getRequestData());
}else{
    //pay fail
}
```

### Query Order [doc](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1)
```php
$response = $gateway->query([
    'transaction_id' => '1217752501201407033233368018', //The wechat trade no
])->send();

var_dump($response->isSuccessful());
var_dump($response->getData());
```


### Close Order [doc](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_3&index=5)
```php
$response = $gateway->close([
    'out_trade_no' => '201602011315231245', //The merchant trade no
])->send();

var_dump($response->isSuccessful());
var_dump($response->getData());
```

### Refund [doc](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_4&index=6)
```php
$gateway->setCertPath($certPath);
$gateway->setKeyPath($keyPath);

$response = $gateway->refund([
    'transaction_id' => '1217752501201407033233368018', //The wechat trade no
    'out_refund_no' => $outRefundNo,
    'total_fee' => 1, //=0.01
    'refund_fee' => 1, //=0.01
])->send();

var_dump($response->isSuccessful());
var_dump($response->getData());
```

### QueryRefund [doc](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_5&index=7)
```php
$response = $gateway->queryRefund([
    'refund_id' => '1217752501201407033233368018', //Your site trade no, not union tn.
])->send();

var_dump($response->isSuccessful());
var_dump($response->getData());
```

### Shorten URL (for `WechatPay_Native`) [doc](https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_9&index=8)
```php
$response = $gateway->shortenUrl([
    'long_url' => $longUrl
])->send();

var_dump($response->isSuccessful());
var_dump($response->getData());
var_dump($response->getShortUrl());
```

### Query OpenId (for `WechatPay_Pos`) [doc](https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_13&index=9)
```php
$response = $gateway->queryOpenId([
    'auth_code' => $authCode
])->send();

var_dump($response->isSuccessful());
var_dump($response->getData());
var_dump($response->getOpenId());
```

For general usage instructions, please see the main [Omnipay](https://github.com/omnipay/omnipay)
repository.

## Related

- [Laravel-Omnipay](https://github.com/ignited/laravel-omnipay)
- [Omnipay-Alipay](https://github.com/lokielse/omnipay-alipay)
- [Omnipay-UnionPay](https://github.com/lokielse/omnipay-unionpay)

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/lokielse/omnipay-wechatpay/issues),
or better yet, fork the library and submit a pull request.
