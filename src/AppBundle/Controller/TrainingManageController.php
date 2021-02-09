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
    public $pageSize=20;
    public function __construct(){
        // 测试请求
        $this->client = new AbstractCloudAPI();
    }
    // 镜像弹窗
    public function imagesPickerAction(Request $request, $id)
    {
        $currentId = $request->query->get("currentId");
        $lists = $this->getData("",$currentId);
        return $this->render('training/manage/images/images-modal.html.twig',[
            'id'=>$id,
            'currentId'=>$currentId,
            'lists'=>$lists,
        ]);
    }

    // 获取镜像列表数据
    public function imagesPickListAction(Request $request,$courseId,$taskId){
        $type = $request->query->get("type");
        $currentId = $request->query->get("currentId");
        $lists = $this->getData($type,$currentId);
        
        return $this->render(
            'training/manage/images/images-list.html.twig',
            ['lists'=>$lists]
        );
    }

    // 获取镜像数据
    private function getData($type,$currentId){
        $lists = [];
        if($type =='public'){
            $lists[] = ['id'=>1,'name'=>'公共镜像-01','checked'=>' '];
            $lists[] = ['id'=>2,'name'=>'公共镜像-02','checked'=>' '];
            $lists[] = ['id'=>3,'name'=>'公共镜像-03','checked'=>' '];
        }else{
            $lists[] = ['id'=>4,'name'=>'个人镜像-01','checked'=>' '];
            $lists[] = ['id'=>5,'name'=>'个人镜像-02','checked'=>' '];
            $lists[] = ['id'=>6,'name'=>'个人镜像-03','checked'=>' '];
        }

        foreach($lists as &$info){
            if($info['id'] == $currentId){
                $info['checked'] = "checked";
            }
        }
        return $lists;
    }

    // 数据集弹窗
    public function datasetPickerAction(Request $request,$id){
        //默认选中数据集，与第一页数据
        $currents = $request->get("current");
        $result = $this->getDataset($request);
    
        return $this->render('training/manage/dataset/dataset-modal.html.twig',[
            'id'=>$id,
            'datasetLists'=>$result['body'],
            'tags'=>$currents,
            'paginator'=>$result['paginator'],
        ]);
    }

    public function datasetInfoPickerAction(Request $request,$id){
        $result = $this->getDataset($request);
        $tags = [
            ['id'=>1,'name'=>'数据集-01'],
            ['id'=>3,'name'=>'数据集-03'],
            ['id'=>12,'name'=>'数据集-12'],
        ];
        return $this->render('training/manage/dataset/dataset-modal-list.html.twig',[
            'id'=>$id,
            'datasetLists'=>$result['body'],
            'tags'=>$tags,
            'paginator'=>$result['paginator'],
        ]);
    }

    // 获取数据集
    public function getDataset($request){
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
