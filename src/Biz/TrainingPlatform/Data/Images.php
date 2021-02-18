<?php

namespace Biz\TrainingPlatform\Data;

use AppBundle\Common\Paginator;
use Biz\TrainingPlatform\Client\AbstractCloudAPI;

/**
 * 镜像相关接口
 */
class Images
{
    public $client;
    public $pageSize=5;
    public function __construct(){
        // 测试请求
        $this->client = new AbstractCloudAPI();
    }

    public function getImagesList($request){
        $return = ['paginator'=>'','body'=>[]];
        $page = $request->query->get("page",1);
        $currentId = $request->query->get("currentId");
        $params = ['page_num'=>$page,'page_size'=>$this->pageSize];
        $result = $this->client->get("api-course/tm/cusimg",$params);
        if($result['status']['code'] == 2000000){
            // 设置默认选中
            foreach($result['body'] as &$info){
                $info['checked'] = "";
                if($info['id'] == $currentId){
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
}