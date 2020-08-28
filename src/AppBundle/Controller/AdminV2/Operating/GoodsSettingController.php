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
            $setting = $request->request->all();
            $this->updateCourseAndClassroomSettingSetting($setting);
            $this->getSettingService()->set('goods_setting', $request->request->all());
        }

        return $this->render('admin-v2/operating/goods-setting/index.html.twig', [
            'setting' => $this->getSettingService()->get('goods_setting', []),
        ]);
    }

    private function updateCourseAndClassroomSettingSetting($setting)
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $courseSetting['show_review'] = $setting['show_review'];
        $this->getSettingService()->set('course', $courseSetting);
        $classroomSetting = $this->getSettingService()->get('classroom', []);
        $classroomSetting['show_review'] = $setting['show_review'];
        $this->getSettingService()->set('classroom', $classroomSetting);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
