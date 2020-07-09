<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class GoodsSettingController extends BaseController
{
    public function indexAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->getSettingService()->set('goods_setting', $request->request->all());
        }

        return $this->render('admin-v2/operating/goods-setting/index.html.twig', [
            'setting' => $this->getSettingService()->get('goods_setting', []),
        ]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
