<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 新增加
use Biz\TrainingPlatform\Data\Dataset;

class DatasetSetController extends BaseController
{
    public $datasetObj = null;
    public function __construct(){
        if(empty($this->datasetObj)){
            $this->datasetObj = new Dataset();
        }
    }
    public function indexAction(Request $request)
    {
        
        $lists = $this->datasetObj->getDatasetList($request);
        return $this->render(
            'admin-v2/teach/dataset/index.html.twig',
            [
                'lists' => $lists['body'],
                'paginator'=>$lists['paginator']
            ]
        );
    }
    // 编辑数据集
    public function editAction(Request $request,$id){
        // 获取数据集信息
        // $info = $this->datasetObj->getInfo($id);
        $info ='{"body":{"id":29,"name":"voice分类","files":[{"id":56,"name":"Voice","size":0,"path":"Voice"}],"remark":"","ctime":"2020-12-29 18:12:44","mtime":"2020-12-29 18:12:44","upload_type":2,"upload_type_name":"FTP","mount_path":"/ilab/datasets/local","create_user":{"id":95,"name":"吴老师"},"mcp_type":2,"mcp_type_name":"本地环境","uuid_rel":"60945396a70e4d37ab6410ca8ec735cb","mcp_endpoint":{"id":1,"name":"浙江理工大学-本地部署","domain_name":"https://zjlg-prid.ilab.cmcm.com:8443"}},"status":{"message":"请求成功","code":2000000}}';
        $treeData = '{"body":[{"name":"大数据","path":"大数据"},{"name":"人工智能","path":"人工智能"},{"name":"LFW","path":"LFW"},{"name":"MNIST","path":"MNIST"},{"name":"cat-dog","path":"cat-dog"}],"status":{"message":"请求成功","code":2000000}}';
        
        // 获取ftp目录信息
        return $this->render(
            'admin-v2/teach/dataset/edit.html.twig',
            [
                "info"=>$info,
                "treeData"=>$treeData,
            ]
        );
    }

    // 获取目录信息 ajax
    public function dirListAction(Request $request){
        $return = [
            'body'=>[
                ['name'=>"美国大选","path"=>"大数据/美国大选","isLeaf"=>false],
                ['name'=>"动物图像","path"=>"大数据/动物图像","isLeaf"=>false]
            ],
            "status"=>[
                "message"=>"成功",
                "code"=>2000000
            ]
        ];
        return $this->createJsonResponse($return);
    }

    ## 删除数据集
    public function deleteAction(){
        return $this->createJsonResponse(['code' => 0, 'message' => '删除数据集成功']);
    }
}
