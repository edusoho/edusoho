<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Question\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;

class TrainingManageController extends BaseController
{
    public function imagesPickerAction(Request $request, $id)
    {
        $currentId = $request->query->get("currentId");
        $lists = $this->getData("",$currentId);
        return $this->render('training/manage/images-modal.html.twig',[
            'id'=>$id,
            'currentId'=>$currentId,
            'lists'=>$lists,
        ]);
    }

    // 获取列表数据
    public function imagesPickListAction(Request $request,$courseId,$taskId){
        $type = $request->query->get("type");
        $currentId = $request->query->get("currentId");
        $lists = $this->getData($type,$currentId);
        
        return $this->render(
            'training/manage/images-list.html.twig',
            ['lists'=>$lists]
        );
    }

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

    public function imagesPickerdAction(Request $request, $id){
        echo 18;die;
    }
}
