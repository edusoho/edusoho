<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Question\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use Biz\TrainingPlatform\Data\Images;

class ImagesManageController extends BaseController
{
    // 镜像弹窗
    public function imagesPickerAction(Request $request, $id)
    {
        $imagesName = $request->query->get("imagesName");
        $imagesVersion = $request->query->get("imagesVersion");
        $lists = $this->getImagesList($request,$imagesName,$imagesVersion);
        return $this->render('training/manage/images/images-modal.html.twig',[
            'id'=>$id,
            'imagesName'=>$imagesName,
            'imagesVersion'=>$imagesVersion,
            'lists'=>$lists['body'],
            'paginator'=>$lists['paginator'],
        ]);
    }

    // 获取镜像列表数据
    public function imagesPickListAction(Request $request,$courseId,$taskId){
        $imagesName = $request->query->get("imagesName");
        $imagesVersion = $request->query->get("imagesVersion");

        $lists = $this->getImagesList($request,$imagesName,$imagesVersion);
        return $this->render(
            'training/manage/images/images-list.html.twig',
            [
                'lists'=>$lists['body'],
                'paginator'=>$lists['paginator']
            ]
        );
    }

    // 获取镜像列表
    private function getImagesList($request,$imagesName="",$imagesVersion=""){
        $return = ['body'=>[]];
        $result = (new Images())->getImagesVersionAllList($request);
        if($result['status']['code'] == 2000000){
            // 设置选中
            foreach($result['body'] as &$info){
                $info['checked'] = "";
                if($info['name'] == $imagesName){
                    $info['checked'] = "checked";
                }
                if($info['tags']){
                    foreach($info['tags'] as &$val){
                        $val['checked'] = "";
                        if($val['tag_name'] == $imagesVersion && $info['name'] == $imagesName){
                            $val['checked'] = "selected";
                        }
                    }
                }
            }
            $return = $result;
        }
        return $return;
    }
}
