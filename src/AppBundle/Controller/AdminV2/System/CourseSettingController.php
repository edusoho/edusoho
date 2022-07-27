<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserFieldService;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class CourseSettingController extends BaseController
{
    public function courseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $liveCourseSetting = $this->getSettingService()->get('live-course', []);
        $userDefaultSetting = $this->getSettingService()->get('user_default', []);
        $courseDefaultSetting = $this->getSettingService()->get('course_default', []);
        $courseDefaultSet = $this->getCourseDefaultSet();
        $defaultSetting = array_merge($courseDefaultSet, $courseDefaultSetting);

        $default = [
            'welcome_message_enabled' => '0',
            'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
            'teacher_manage_marketing' => '0',
            'teacher_search_order' => '0',
            'teacher_manage_student' => '0',
            'teacher_course_material_download' => '1',
            'teacher_export_student' => '0',
            'explore_default_orderBy' => 'latest',
            'free_course_nologin_view' => '1',
            'relatedCourses' => '0',
            'coursesPrice' => '0',
            'allowAnonymousPreview' => '1',
            'copy_enabled' => '0',
            'doc_water_mark_enabled' => 0,
            'doc_water_mark_info' => '',
            'testpaperCopy_enabled' => '0',
            'custom_chapter_enabled' => '0',
            'show_cover_num_mode' => 'studentNum',
            'show_review' => '1',
        ];

        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('live-course', $liveCourseSetting);

        $threadEnabled = $this->getSettingService()->node('ugc_thread.enable_thread', '1');
        $noteEnabled = $this->getSettingService()->node('ugc_note.enable_note', '1');
        $courseSetting = array_merge($default, $courseSetting, [
            'show_question' => $threadEnabled ?
                $this->getSettingService()->node('ugc_thread.enable_course_question', '1') :
                '0',
            'show_discussion' => $threadEnabled ?
                $this->getSettingService()->node('ugc_thread.enable_course_thread', '1') :
                '0',
            'show_note' => $noteEnabled ?
                $this->getSettingService()->node('ugc_note.enable_course_note', '1') :
                '0',
        ]);

        if ('POST' == $request->getMethod()) {
            $defaultSetting = $request->request->all();
            $courseDefaultSetting = [
                'custom_chapter_enabled' => 0,
                'chapter_name' => '章',
                'part_name' => '节',
                'task_name' => '任务',
            ];

            $courseDefaultSetting = array_merge($courseDefaultSetting, $defaultSetting);
            $this->getSettingService()->set('course_default', $courseDefaultSetting);

            $default = $this->getSettingService()->get('default', []);
            $defaultSetting = array_merge($default, $userDefaultSetting, $courseDefaultSetting);
            $this->getSettingService()->set('default', $defaultSetting);
            $magic = $this->getSettingService()->get('magic', []);
            if ($defaultSetting['doc_water_mark_enabled'] && $defaultSetting['doc_water_mark_info']) {
                $waterMark = '';
                foreach ($defaultSetting['doc_water_mark_info'] as $item) {
                    $waterMark .= "{{{$item}}}";
                }
                $magic['doc_watermark'] = $waterMark;
            } else {
                unset($magic['doc_watermark']);
            }
            $this->getSettingService()->set('magic', $magic);

            $courseUpdateSetting = array_merge($courseDefaultSetting, $request->request->all());

            $courseSetting = array_merge($courseSetting, $courseUpdateSetting, $liveCourseSetting);

            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $courseSetting);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/system/course-setting/course-setting.html.twig', [
            'courseSetting' => $courseSetting,
            'defaultSetting' => $defaultSetting,
            'hasOwnCopyright' => false,
        ]);
    }

    public function courseAvatarAction(Request $request)
    {
        $defaultSetting = $this->getSettingService()->get('default', []);

        if ('POST' == $request->getMethod()) {
            $courseDefaultSetting = $request->request->get('defaultCoursePicture', 0);
            $defaultSetting = array_merge($defaultSetting, ['defaultCoursePicture' => $courseDefaultSetting]);

            $this->getSettingService()->set('default', $defaultSetting);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('admin_v2_setting_course_avatar'));
        }

        return $this->render('admin-v2/system/course-setting/course-avatar.html.twig', [
            'defaultSetting' => $defaultSetting,
            'hasOwnCopyright' => false,
        ]);
    }

    public function liveCourseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', []);
        $liveCourseSetting = $this->getSettingService()->get('live-course', []);
        $client = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        $default = [
            'live_course_enabled' => '0',
        ];

        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('live-course', $liveCourseSetting);
        $setting = array_merge($default, $liveCourseSetting);

        if ('POST' == $request->getMethod()) {
            $liveCourseSetting = $request->request->all();
            $liveCourseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];
            $setting = array_merge($courseSetting, $liveCourseSetting);
            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $setting);

            $hiddenMenus = $this->getSettingService()->get('menu_hiddens', []);

            if ($liveCourseSetting['live_course_enabled']) {
                unset($hiddenMenus['admin_v2_live_course_add']);
                unset($hiddenMenus['admin_v2_live_course']);
            } else {
                $hiddenMenus['admin_v2_live_course_add'] = true;
                $hiddenMenus['admin_v2_live_course'] = true;
            }

            $this->getSettingService()->set('menu_hiddens', $hiddenMenus);

            $this->getLogService()->info('admin-v2/system/course-setting', 'update_settings', '更新课程设置', $setting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $setting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

        return $this->render('admin-v2/system/course-setting/live-course-setting.html.twig', [
            'courseSetting' => $setting,
            'capacity' => $capacity,
        ]);
    }

    public function questionsSettingAction(Request $request)
    {
        $questionsSetting = $this->getSettingService()->get('questions', []);

        if (empty($questionsSetting)) {
            $default = [
                'testpaper_answers_show_mode' => 'submitted',
            ];
            $questionsSetting = $default;
        }

        if ('POST' == $request->getMethod()) {
            $questionsSetting = $request->request->all();
            $this->getSettingService()->set('questions', $questionsSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin-v2/system/course-setting/questions-setting.html.twig');
    }

    public function coursePurchaseAgreementAction(Request $request)
    {
        $purchaseAgreement = $this->getSettingService()->get('course_purchase_agreement');
        if ($request->isMethod('POST')) {
            $purchaseAgreement['title'] = $request->request->get('purchaseAgreementTitle');
            $purchaseAgreement['content'] = $request->request->get('purchaseAgreementContent');
            $this->getSettingService()->set('course_purchase_agreement', $purchaseAgreement);
        }

        return $this->render('admin-v2/system/course-setting/course-purchase-agreement.html.twig', [
            'purchaseAgreement' => $purchaseAgreement,
        ]);
    }

    protected function getCourseDefaultSet()
    {
        $default = [
            'defaultCoursePicture' => 0,
            'defaultCoursePictureFileName' => 'coursePicture',
            'articleShareContent' => '我正在看{{articletitle}}，关注{{sitename}}，分享知识，成就未来。',
            'courseShareContent' => '我正在学习{{course}}，收获巨大哦，一起来学习吧！',
            'groupShareContent' => '我在{{groupname}}小组,发表了{{threadname}},很不错哦,一起来看看吧!',
            'classroomShareContent' => '我正在学习{{classroom}}，收获巨大哦，一起来学习吧！',
            'chapter_name' => '章',
            'part_name' => '节',
        ];

        return $default;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }
}
