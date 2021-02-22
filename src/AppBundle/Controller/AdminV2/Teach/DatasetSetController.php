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
    // 编辑新增数据集
    public function editAction(Request $request)
    {
        $id = $request->query->get("id");
        $info = ['body' => []];
        if (!empty($id)) {
            // 获取详情
            $info = $this->datasetObj->getInfo($id);
        }
        // 获取ftp目录树
        $localDir = $this->datasetObj->getLocaldir();

        // 获取ftp目录信息
        return $this->render(
            'admin-v2/teach/dataset/edit.html.twig',
            [
                "info" => json_encode($info['body']),
                "treeData" => json_encode($localDir['body']),
            ]
        );
    }

    // 提交数据集
    public function submitAction(Request $request)
    {
        $return = ['body' => [], 'status' => ['code' => 5000000]];
        $params = $request->getContent();
        $params = json_decode($params, true);
        $user = $this->getCurrentUser();
        $data = [
            'name' => $params['title'],
            'create_user_id' => $user->id,
            'files' => $params['paths'],
            'remark' => $params['remark']
        ];
        // 修改
        if ($params['id'] > 0) {
            $result = $this->datasetObj->update($params['id'], $data);
        } else { // 新增
            $result = $this->datasetObj->add($data);
        }
        if ($result['status']['code'] == 2000000) {
            $return = $result;
        }
        return $this->createJsonResponse($return);
    }

    // 获取目录信息 ajax
    public function dirListAction(Request $request)
    {
        $return = ['body' => []];
        $path = $request->query->get("path");
        $localDir = $this->datasetObj->getLocaldir($path);
        if ($localDir['status']['code'] == 2000000) {
            $return = $localDir;
        }
        return $this->createJsonResponse($return['body']);
    }

    ## 删除数据集
    public function deleteAction(Request $request, $id)
    {
        $return = ['body' => []];
        $result = $this->datasetObj->delete($id);
        if ($result['status']['code'] == 2000000) {
            $return = $result;
        }
        return $this->createJsonResponse($return);
    }
}
