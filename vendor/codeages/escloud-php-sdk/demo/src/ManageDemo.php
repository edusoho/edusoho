<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ESCloud\SDKDemo\Permission\Permission;
use ESCloud\SDKDemo\Sdk\Sdk;

$action = $_GET['action'];

switch ($action) {
    case 'getResource':
        getResource();
        break;
    case 'searchResources':
        searchResources();
        break;
    case 'renameResource':
        renameResource();
        break;
    case 'deleteResource':
        deleteResource();
        break;
    case 'getDownloadUrl':
        getDownloadUrl();
        break;
    case 'getThumbnails':
        getThumbnails();
        break;
}

/*
 * 资源获取接口
 */
function getResource()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $no = $_GET['no'];

    echo json_encode($sdk->getResourceService()->get($no));

}

/*
 * 批量查询接口
 */
function searchResources()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $param = array();
    if (!empty($_GET['type'])) {
        $param['type'] = $_GET['type'];
    }

    echo json_encode($sdk->getResourceService()->search($param));
}

/*
 * 删除资源接口
 */
function deleteResource()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $no = $_POST['no'];

    echo json_encode($sdk->getResourceService()->delete($no));

}

/*
 * 获取下载地址接口
 */
function getDownloadUrl()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $no = $_POST['no'];

    echo json_encode($sdk->getResourceService()->getDownloadUrl($no));

}

/*
 * 重命名接口
 */
function renameResource()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();

    $no = $_POST['no'];
    $name = $_POST['name'];

    echo json_encode($sdk->getResourceService()->rename($no, $name));
}

/*
 * 获取缩略图接口
 */
function getThumbnails()
{
    Permission::check($_GET['exp'], $_GET['token']);

    $sdk = Sdk::init();
    $no = $_GET['no'];

    echo json_encode($sdk->getResourceService()->getThumbnails($no));
}

