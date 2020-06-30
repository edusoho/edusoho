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

function startUpload()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $params['name'] = $_POST['name'];
    $params['extno'] = $_POST['extno'];

    echo json_encode($sdk->getResourceService()->startUpload($params));
}

function finishUpload()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $no = $_POST['no'];

    echo json_encode($sdk->getResourceService()->finishUpload($no));
}
