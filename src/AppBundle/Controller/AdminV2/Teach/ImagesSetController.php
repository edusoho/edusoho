<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 新增加
use Biz\TrainingPlatform\Data\Images;

class ImagesSetController extends BaseController
{
    public function indexAction(Request $request)
    {

        $lists = (new Images())->getImagesList($request);
        return $this->render(
            'admin-v2/teach/images/index.html.twig',
            [
                'lists' => $lists['body'],
                'paginator' => $lists['paginator']
            ]
        );
    }

    // 镜像详情
    public function infoAction()
    {
        return $this->render(
            'admin-v2/teach/images/info.html.twig'
        );
    }

    // 删除镜像
    public function deleteAction()
    {
        return $this->createJsonResponse(['code' => 0, 'message' => '删除镜像成功']);
    }
}
