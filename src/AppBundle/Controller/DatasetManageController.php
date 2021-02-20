<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Question\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use Biz\TrainingPlatform\Data\Dataset;

class DatasetManageController extends BaseController
{
    // 数据集弹窗
    public function datasetPickerAction(Request $request, $id)
    {
        //默认选中数据集，与第一页数据
        $currents = $request->get("current");
        $result = $this->getDatasetList($request);

        return $this->render('training/manage/dataset/dataset-modal.html.twig', [
            'id' => $id,
            'datasetLists' => $result['body'],
            'tags' => $currents,
            'paginator' => $result['paginator'],
        ]);
    }

    public function datasetInfoPickerAction(Request $request, $id)
    {
        $result = $this->getDatasetList($request);
        return $this->render('training/manage/dataset/dataset-modal-list.html.twig', [
            'id' => $id,
            'datasetLists' => $result['body'],
            'paginator' => $result['paginator'],
        ]);
    }

    // 获取数据集列表
    private function getDatasetList($request)
    {
        $return = (new Dataset())->getDatasetList($request);
        return $return;
    }
}
