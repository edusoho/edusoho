<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ESCloud\SDKDemo\Permission\Permission;
use ESCloud\SDKDemo\Sdk\Sdk;

$action = $_GET['action'];

switch ($action) {
    case 'startUpload':
        startUpload();
        break;
    case 'finishUpload':
        finishUpload();
        break;
}

/*
 * 上传开始接口
 * 如果上传字幕，请多带directives参数，参数如下：
 * $params['directives'] = [
        'output' => 'caption',
        'videoNo' => '00afd8287d7f49c4a3f0a5aec50b7c99'
    ];
    videoNo：是具体的把字幕绑定到哪个视频下面的视频no
 */
function startUpload()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $params['name'] = $_POST['name'];
    $params['extno'] = $_POST['extno'];

    echo json_encode($sdk->getResourceService()->startUpload($params));
}

/*
 * 上传完成接口
 */
function finishUpload()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $no = $_POST['no'];

    echo json_encode($sdk->getResourceService()->finishUpload($no));
}
