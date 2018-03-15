# QiQiuYun PHP SDK

[![Build Status](https://travis-ci.org/codeages/qiqiuyun-php-sdk.svg?branch=master)](https://travis-ci.org/codeages/qiqiuyun-php-sdk)

## 安装

```shell
composer require codeages/qiqiuyun-php-sdk
```

## 使用说明

```php
$sdk = new \QiQiuYun\SDK\QiQiuYunSDK(array(
    'access_key' => 'your_access_key', // 必需
    'secret_key' => 'your_secret_key', // 必需
    'service' => array(     // 可选，各个服务的配置项
        'xapi' => array(    // 每个服务，都有自己的必需的配置项，如需调用则必需配置该服务的配置项
            'school_name' => '测试网校',
        )
    )
));

// 获取短信服务
$sdk->getSmsService();

// 获取云资源播放服务
$sdk->getPlayService();

// 获取XAPI服务
$sdk->getXAPIService();

// 获取分销服务
$sdk->getDrpService();
```

气球云为网校接入提供了测试环境，用于发调试。通过设置`host`配置项，即可使用测试环境的服务，例如短信测试服务：

```php
$sdk = new \QiQiuYun\SDK\QiQiuYunSDK(array(
    'access_key' => 'your_access_key', 
    'secret_key' => 'your_secret_key', 
    'service' => array(
        'sms' => array(
            'host' => 'sms-service.test.qiqiuyun.net',
        )
    )
));

$sdk->getSmsService();
```

## 变更日志

见 [CHANGELOG.md](CHANGELOG.md) 。
