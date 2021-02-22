<?php

namespace Biz\TrainingPlatform\Data;

use AppBundle\Common\Paginator;
use Biz\TrainingPlatform\Client\AbstractCloudAPI;
use Biz\TrainingPlatform\Data\Base;

/**
 * 镜像相关接口
 */
class Images extends Base
{
    public $client;
    public $pageSize = 5;
    const APILIST = [
        "list" => "tm/imgs",                          // 获取仓库列表
        "list_all" => "tm/img/list",                      // 获取完整仓库镜像列表
        "version_list" => "tm/img/{img_repo}/tags",           // 仓库下版本列表
        'version_delete' => "tm/img/{img_repo}/tag"             // 删除仓库下版本
    ];

    public function __construct()
    {
        $this->client = new AbstractCloudAPI();
    }

    // 获取镜像仓库列表
    public function getImagesList($request)
    {
        $return = ['paginator' => '', 'body' => []];
        $page = $request->query->get("page", 1);
        $currentId = $request->query->get("currentId");
        $params = ['page_num' => $page, 'page_size' => $this->pageSize];
        $result = $this->client->get(self::APILIST['list'], $params);
        if ($result['status']['code'] == 2000000) {
            // 设置默认选中
            foreach ($result['body'] as &$info) {
                $info['checked'] = "";
                if ($info['id'] == $currentId) {
                    $info['checked'] = "checked";
                }
            }

            $pageNum = $result['page']['count'];
            $return['paginator'] = new Paginator(
                $request,
                $pageNum,
                $this->pageSize
            );
            $return['body'] = $result['body'];
        }
        return $return;
    }

    // 获取仓库下版本列表
    public function getImagesVersionList($request, $img_repo = "")
    {
        $return = ['paginator' => '', 'body' => []];
        $page = $request->query->get("page", 1);
        $currentId = $request->query->get("currentId");
        $params = ['page_num' => $page, 'page_size' => $this->pageSize];
        $result = $this->client->get(str_replace("{img_repo}", $img_repo, self::APILIST['version_list']), $params);
        if ($result['status']['code'] == 2000000) {
            // 设置默认选中
            foreach ($result['body'] as &$info) {
                $info['checked'] = "";
                if ($info['id'] == $currentId) {
                    $info['checked'] = "checked";
                }
            }

            $pageNum = $result['page']['count'];
            $return['paginator'] = new Paginator(
                $request,
                $pageNum,
                $this->pageSize
            );
            $return['body'] = $result['body'];
        }
        return $return;
    }

    // 获取完整镜像列表
    public function getImagesVersionAllList($request)
    {
        $return = ['paginator' => '', 'body' => [], 'status' => ['code' => 5000000]];
        $page = $request->query->get("page", 1);
        $currentId = $request->query->get("currentId");
        $params = ['page_num' => $page, 'page_size' => $this->pageSize];
        $result = $this->client->get(self::APILIST['list_all'], $params);
        if ($result['status']['code'] == 2000000) {
            // 设置默认选中
            foreach ($result['body'] as &$info) {
                $info['checked'] = "";
                if ($info['id'] == $currentId) {
                    $info['checked'] = "checked";
                }
            }

            $pageNum = $result['page']['count'];
            $return['paginator'] = new Paginator(
                $request,
                $pageNum,
                $this->pageSize
            );
            $return['body'] = $result['body'];
            $return['status'] = $result['status'];
        }
        return $return;
    }
    // 删除镜像仓库
    public function deleteVersion($name, $vname)
    {
        $params['tag_name'] = $vname;
        $result = $this->client->delete(str_replace("{img_repo}", $name, self::APILIST['version_delete']), $params);
        if ($result['status']['code'] == 2000000) {
            $this->return = $result;
        }
        return $this->return;
    }
}