<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Question\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;

class TrainingManageController extends BaseController
{
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
        $paginator = $this->getDataset($request);
        //默认选中数据集，与第一页数据
        $datasetLists = [
            ['id'=>1,'name'=>"数据集-01","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>2,'name'=>"数据集-02","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>3,'name'=>"数据集-03","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>4,'name'=>"数据集-04","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>5,'name'=>"数据集-05","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>6,'name'=>"数据集-06","desc"=>"描述1","teacher"=>"李老师"],
        ];
        $tags = [
            ['id'=>1,'name'=>'数据集-01'],
            ['id'=>3,'name'=>'数据集-03'],
            ['id'=>4,'name'=>'数据集-04'],
            ['id'=>12,'name'=>'数据集-12'],
        ];



        return $this->render('training/manage/dataset/dataset-modal.html.twig',[
            'id'=>$id,
            'datasetLists'=>$datasetLists,
            'tags'=>$tags,
            'paginator'=>$paginator,
        ]);
    }

    public function datasetInfoPickerAction(Request $request,$id){
        $paginator = $this->getDataset($request);

        $datasetLists = [
            ['id'=>7,'name'=>"数据集-07","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>8,'name'=>"数据集-08","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>9,'name'=>"数据集-09","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>10,'name'=>"数据集-10","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>11,'name'=>"数据集-11","desc"=>"描述1","teacher"=>"李老师"],
            ['id'=>12,'name'=>"数据集-12","desc"=>"描述1","teacher"=>"李老师"],
        ];

        $tags = [
            ['id'=>1,'name'=>'数据集-01'],
            ['id'=>3,'name'=>'数据集-03'],
            ['id'=>4,'name'=>'数据集-04'],
            ['id'=>12,'name'=>'数据集-12'],
        ];

        return $this->render('training/manage/dataset/dataset-modal-list.html.twig',[
            'id'=>$id,
            'datasetLists'=>$datasetLists,
            'tags'=>$tags,
            'paginator'=>$paginator,
        ]);
    }

    public function getDataset($request){
        $paginator = new Paginator(
            $request,
            100,
            10
        );
        return $paginator;
    }
}
