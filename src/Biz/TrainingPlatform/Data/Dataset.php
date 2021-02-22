<?php

namespace Biz\TrainingPlatform\Data;

use AppBundle\Common\Paginator;
use Biz\TrainingPlatform\Data\Base;
use Biz\TrainingPlatform\Client\AbstractCloudAPI;

/**
 * 数据集相关接口
 */
class Dataset extends Base
{
    public $client;
    public $pageSize = 5;
    const APILIST = [
        "list" => 'tm/datasets',              // 获取数据集列表
        'info' => 'tm/dataset/{id}',          //获取数据集详情
        'localDir' => 'tm/datasets/localdir',     //获取ftp目录树
        'update' => 'tm/dataset/{id}',          // 修改数据集
        'add' => 'tm/datasets',              // 修改数据集
        'delete' => 'tm/dataset/{id}',          // 删除数据集
    ];

    public function __construct()
    {
        $this->client = new AbstractCloudAPI();
    }

    // 获取数据集列表
    public function getDatasetList($request)
    {
        $return = ['paginator' => '', 'body' => []];
        $page = $request->query->get("page", 1);
        $params = ['page_num' => $page, 'page_size' => $this->pageSize];
        $result = $this->client->get(self::APILIST['list'], $params);
        if ($result['status']['code'] == 2000000) {
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

    // 获取数据集信息
    public function getInfo($id)
    {
        if (!empty($id)) {
            $result = $this->client->get(str_replace("{id}", $id, self::APILIST['info']));
            if ($result['status']['code'] == 2000000) {
                $this->return = $result;
            }
        }
        return $this->return;
    }

    // 获取数据集ftp目录结构
    public function getLocaldir($path = "")
    {
        $params = [];
        if (!empty($path)) {
            $params['path'] = $path;
        }
        $result = $this->client->get(self::APILIST['localDir'], $params);
        if ($result['status']['code'] == 2000000) {
            $this->return = $result;
        }
        return $this->return;
    }

    // 修改数据集
    public function update($id, $data = [])
    {
        $result = $this->client->put(str_replace("{id}", $id, self::APILIST['update']), $data);
        if ($result['status']['code'] == 2000000) {
            $this->return = $result;
        }
        return $this->return;
    }

    // 新增数据集
    public function add($data = [])
    {
        $result = $this->client->post(self::APILIST['add'], $data);
        if ($result['status']['code'] == 2000000) {
            $this->return = $result;
        }
        return $this->return;
    }

    // 删除数据集
    public function delete($id)
    {
        $result = $this->client->delete(str_replace("{id}", $id, self::APILIST['delete']));
        if ($result['status']['code'] == 2000000) {
            $this->return = $result;
        }
        return $this->return;
    }
}