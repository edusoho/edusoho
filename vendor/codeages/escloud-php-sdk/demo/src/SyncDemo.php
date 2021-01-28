<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ESCloud\SDKDemo\Permission\Permission;
use ESCloud\SDKDemo\Sdk\Sdk;

$action = $_GET['action'];

switch ($action) {
    case 'syncResources':
        syncResources();
        break;
}

/*
 * 转码状态同步接口
 * cursor 时间戳，单位秒
 *
 */
function syncResources()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $param = array();
    if (!empty($_GET['cursor'])) {
        $param['cursor'] = $_GET['cursor'];
    }

    echo json_encode($sdk->getResourceService()->sync($param));
}

