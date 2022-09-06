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
            $reviewEnabled = $this->getSettingService()->node('ugc_review.enable_review', '1');
            $setting['show_review'] = $reviewEnabled && ($this->getSettingService()->node('ugc_review.enable_course_review', '1')
                || $this->getSettingService()->node('ugc_review.enable_classroom_review', '1')) ?
                1 :
                0;
            $this->updateCourseAndClassroomSettingSetting($setting);
            $this->getSettingService()->set('goods_setting', $request->request->all());
        }

        return $this->render('admin-v2/operating/goods-setting/index.html.twig', [
            'setting' => $this->getSettingService()->get('goods_setting', []),
        ]);
    }

    private function updateCourseAndClassroomSettingSetting($setting)
    {
        //课程 商品剥离设置项整合到此处，需要在此处生效后同步更新到课程设置（时间：2020-10-20，版本： 20.4.3）
        $courseSetting = $this->getSettingService()->get('course', []);
        $courseSetting['show_review'] = $setting['show_review'];
        //是否显示课程人数
        $courseSetting['show_student_num_enabled'] = !empty($setting['show_number_data']) && 'none' === $setting['show_number_data'] ? 0 : 1;
        //显示加入数还是点击数，如果为none，则为缺省值studentNum
        if (empty($setting['show_number_data'])) {
            $courseSetting['show_cover_num_mode'] = 'studentNum';
        } else {
            $courseSetting['show_cover_num_mode'] = 'visitor' === $setting['show_number_data'] ? 'hitNum' : 'studentNum';
        }
        $courseSetting['show_student_review_num_enable'] = $setting['show_student_review_num_enable'];
        $this->getSettingService()->set('course', $courseSetting);

        //班级 商品剥离设置项整合到此处，需要在此处生效后同步更新到班级设置（时间：2020-10-20，版本： 20.4.3）
        $classroomSetting = $this->getSettingService()->get('classroom', []);
        $classroomSetting['show_review'] = $setting['show_review'];
        //是否显示班级人数，只要商品设置为显示任何一种，班级都将是否显示班级人数设置为1
        $classroomSetting['show_student_num_enabled'] = !empty($setting['show_number_data']) && 'join' === $setting['show_number_data'] ? 1 : 0;
        $classroomSetting['show_hit_num_enabled'] = !empty($setting['show_number_data']) && 'visitor' === $setting['show_number_data'] ? 1 : 0;
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
