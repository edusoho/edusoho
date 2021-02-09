<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Question\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use Biz\TrainingPlatform\Client\AbstractCloudAPI;

class TrainingManageController extends BaseController
{
    public $client;
    public $pageSize=5;
    public function __construct(){
        // 测试请求
        $this->client = new AbstractCloudAPI();
    }
    // 镜像弹窗
    public function imagesPickerAction(Request $request, $id)
    {
        $currentId = $request->query->get("currentId");
        $lists = $this->getImagesList($request);
        return $this->render('training/manage/images/images-modal.html.twig',[
            'id'=>$id,
            'currentId'=>$currentId,
            'lists'=>$lists['body'],
            'paginator'=>$lists['paginator'],
        ]);
    }

    // 获取镜像列表数据
    public function imagesPickListAction(Request $request,$courseId,$taskId){
        $lists = $this->getImagesList($request);
        return $this->render(
            'training/manage/images/images-list.html.twig',
            [
                'lists'=>$lists['body'],
                'paginator'=>$lists['paginator']
            ]
        );
    }

    // 获取镜像列表
    private function getImagesList($request){
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

    // 数据集弹窗
    public function datasetPickerAction(Request $request,$id){
        //默认选中数据集，与第一页数据
        $currents = $request->get("current");
        $result = $this->getDatasetList($request);
    
        return $this->render('training/manage/dataset/dataset-modal.html.twig',[
            'id'=>$id,
            'datasetLists'=>$result['body'],
            'tags'=>$currents,
            'paginator'=>$result['paginator'],
        ]);
    }

    public function datasetInfoPickerAction(Request $request,$id){
        $result = $this->getDatasetList($request);
        return $this->render('training/manage/dataset/dataset-modal-list.html.twig',[
            'id'=>$id,
            'datasetLists'=>$result['body'],
            'paginator'=>$result['paginator'],
        ]);
    }

    // 获取数据集列表
    private function getDatasetList($request){
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
}
