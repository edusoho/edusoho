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
    public $pageSize=5;
    public function __construct(){
        // 测试请求
        $this->client = new AbstractCloudAPI();
    }
    // 获取数据集列表
    public function getDatasetList($request){
        $return = ['paginator'=>'','body'=>[]];
        $page = $request->query->get("page",1);
        $params = ['page_num'=>$page,'page_size'=>$this->pageSize];
        $result = $this->client->get("api-competition/tm/ccompet/ds",$params);
        if($result['status']['code'] == 2000000){
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
    public function getInfo($id){
        if(!empty($id)){
            $result = $this->client->get("api-course/tm/ccompet/ds/{$id}");
            if($result['status']['code'] == 2000000){
                $this->return = $result;
            }
        }
        return $this->return;
    }

    // 获取数据集ftp目录结构
    public function getLocaldir($path=""){
        if(!empty($path)){
            $params['path'] = $path;
        }
        $result = $this->client->get("agentsvc/tm/ccompet/ds/localdir",$params);
        if($result['status']['code'] == 2000000){
            $this->return = $result;
        }
        return $this->return;
    }
}