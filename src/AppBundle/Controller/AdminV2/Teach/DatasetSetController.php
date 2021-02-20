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

    public function __construct()
    {
        if (empty($this->datasetObj)) {
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
                'paginator' => $lists['paginator']
            ]
        );
    }
    // 编辑数据集
    public function editAction(Request $request, $id)
    {
        // 获取数据集信息
        // $info = $this->datasetObj->getInfo($id);
        $info = '{"id":29,"name":"voice分类","files":[{"id":56,"name":"LFW","size":0,"path":"LFW"},{"id":55,"name":"大数据/动物图像","size":0,"path":"大数据/动物图像"},{"id":57,"name":"cat-dog","size":0,"path":"cat-dog"}],"remark":"","ctime":"2020-12-29 18:12:44","mtime":"2020-12-29 18:12:44","mount_path":"/ilab/datasets/local"}';
        $treeData = '{"body":[{"name":"大数据","path":"大数据"},{"name":"人工智能","path":"人工智能"},{"name":"LFW","path":"LFW"},{"name":"MNIST","path":"MNIST"},{"name":"cat-dog","path":"cat-dog"}],"status":{"message":"请求成功","code":2000000}}';

        // 获取ftp目录信息
        return $this->render(
            'admin-v2/teach/dataset/edit.html.twig',
            [
                "info" => $info,
                "treeData" => $treeData,
            ]
        );
    }

    public function submitAction(Request $request)
    {
        $conditions = $request->getContent();
        return $this->createJsonResponse(['code' => 0, 'message' => '删除数据集成功']);
    }

    // 获取目录信息 ajax
    public function dirListAction(Request $request)
    {
        $return = [
            'body' => [
                ['name' => "美国大选", "path" => "大数据/美国大选", "isLeaf" => false],
                ['name' => "动物图像", "path" => "大数据/动物图像", "isLeaf" => false]
            ],
            "status" => [
                "message" => "成功",
                "code" => 2000000
            ]
        ];
        return $this->createJsonResponse($return);
    }

    ## 删除数据集
    public function deleteAction()
    {
        return $this->createJsonResponse(['code' => 0, 'message' => '删除数据集成功']);
    }
}
