<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 新增加
use Biz\TrainingPlatform\Data\Dataset;

class DatasetSetController extends BaseController
{
    public function indexAction(Request $request)
    {
        
        $lists = (new Dataset())->getDatasetList($request);
        return $this->render(
            'admin-v2/teach/dataset/index.html.twig',
            [
                'lists' => $lists['body'],
                'paginator'=>$lists['paginator']
            ]
        );
    }
    // 编辑数据集
    public function editAction(){
        return $this->render(
            'admin-v2/teach/dataset/edit.html.twig'
        );
    }
    ## 删除数据集
    public function deleteAction(){
        return $this->createJsonResponse(['code' => 0, 'message' => '删除数据集成功']);
    }
}
