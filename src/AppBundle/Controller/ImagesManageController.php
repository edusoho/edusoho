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
        $currentId = $request->query->get("currentId");
        $lists = $this->getImagesList($request);
        return $this->render('training/manage/images/images-modal.html.twig', [
            'id' => $id,
            'currentId' => $currentId,
            'lists' => $lists['body'],
            'paginator' => $lists['paginator'],
        ]);
    }

    // 获取镜像列表数据
    public function imagesPickListAction(Request $request, $courseId, $taskId)
    {
        $lists = $this->getImagesList($request);
        return $this->render(
            'training/manage/images/images-list.html.twig',
            [
                'lists' => $lists['body'],
                'paginator' => $lists['paginator']
            ]
        );
    }

    // 获取镜像列表
    private function getImagesList($request)
    {
        $return = (new Images())->getImagesList($request);
        return $return;
    }
}
