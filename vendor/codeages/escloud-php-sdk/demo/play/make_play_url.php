<?php
require __DIR__ . '/../../vendor/autoload.php';

use ESCloud\SDK\ESCloudSDK;

$config =  require  __DIR__  . '/../config.php';
$sdk = new ESCloudSDK($config);

$url = $sdk->getPlayService()->makePlayUrl(
    'dfb09bf05dbb400d90924f1e3e821b0c', // resNo,
    600 // lifetime,
);

print_r($url);

// 生成直接返回播放m3u8 json格式的播放列表
$url = $sdk->getPlayService()->makePlayUrl(
    'dfb09bf05dbb400d90924f1e3e821b0c', // resNo,
    600, // lifetime,
    array('native' => 1) // 参见：http://docs.qiqiuyun.com/v2/resource/play.html
);

print_r($url);
