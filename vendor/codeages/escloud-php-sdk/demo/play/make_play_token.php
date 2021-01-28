<?php
require __DIR__ . '/../../vendor/autoload.php';

use ESCloud\SDK\ESCloudSDK;

$config =  require  __DIR__  . '/../config.php';
$sdk = new ESCloudSDK($config);

$token = $sdk->getPlayService()->makePlayToken(
    'dfb09bf05dbb400d90924f1e3e821b0c', // resNo,
    600, // lifetime,
    array() // 参见：http://docs.qiqiuyun.com/v2/resource/play.html
);

print_r($token);
