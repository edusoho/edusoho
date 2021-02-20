<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 新增加
use Biz\TrainingPlatform\Data\Images;

class ImagesSetController extends BaseController
{
    public $imagesObj = null;

    public function __construct()
    {
        if (empty($this->imagesObj)) {
            $this->imagesObj = new Images();
        }
    }

    public function indexAction(Request $request)
    {

        $lists = $this->imagesObj->getImagesList($request);
        return $this->render(
            'admin-v2/teach/images/index.html.twig',
            [
                'lists' => $lists['body'],
                'paginator' => $lists['paginator']
            ]
        );
    }

    // 镜像详情
    public function infoAction(Request $request, $id)
    {
        $lists = $this->imagesObj->getImagesVersionList($request, $id);
        // 获取镜像下面版本号
        return $this->render(
            'admin-v2/teach/images/info.html.twig',
            [
                'lists' => $lists['body'],
                'image_name' => $id,
                'paginator' => $lists['paginator'],
            ]
        );
    }

    // 镜像删除
    public function versionDeleteAction(Request $request, $name, $vname)
    {
        $result = $this->imagesObj->deleteVersion($name, $vname);
        return $this->createJsonResponse($result);
    }
}
