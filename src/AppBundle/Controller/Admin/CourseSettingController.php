<?php

namespace AppBundle\Controller\Admin;

use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;

class CourseSettingController extends BaseController
{
    public function courseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', array());
        $liveCourseSetting = $this->getSettingService()->get('live-course', array());
        $userDefaultSetting = $this->getSettingService()->get('user_default', array());
        $courseDefaultSetting = $this->getSettingService()->get('course_default', array());
        $courseDefaultSet = $this->getCourseDefaultSet();
        $defaultSetting = array_merge($courseDefaultSet, $courseDefaultSetting);

        $default = array(
            'welcome_message_enabled' => '0',
            'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
            'teacher_manage_marketing' => '0',
            'teacher_search_order' => '0',
            'teacher_manage_student' => '0',
            'teacher_export_student' => '0',
            'explore_default_orderBy' => 'latest',
            'free_course_nologin_view' => '1',
            'relatedCourses' => '0',
            'coursesPrice' => '0',
            'allowAnonymousPreview' => '1',
            'copy_enabled' => '0',
            'testpaperCopy_enabled' => '0',
            'custom_chapter_enabled' => '0',
            'show_cover_num_mode' => 'studentNum',
            'show_review' => '1',
            'show_question' => '1',
            'show_discussion' => '1',
            'show_note' => '1',
        );

        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('live-course', $liveCourseSetting);
        $courseSetting = array_merge($default, $courseSetting);

        if ('POST' == $request->getMethod()) {
            $defaultSetting = $request->request->all();

            $courseDefaultSetting = array(
                'custom_chapter_enabled' => 0,
                'chapter_name' => '章',
                'part_name' => '节',
                'task_name' => '任务',
            );

            $courseDefaultSetting = array_merge($courseDefaultSetting, $defaultSetting);
            $this->getSettingService()->set('course_default', $courseDefaultSetting);

            $default = $this->getSettingService()->get('default', array());
            $defaultSetting = array_merge($default, $userDefaultSetting, $courseDefaultSetting);
            $this->getSettingService()->set('default', $defaultSetting);

            $courseUpdateSetting = $request->request->all();

            $courseSetting = array_merge($courseSetting, $courseUpdateSetting, $liveCourseSetting);

            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $courseSetting);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->createJsonResponse(true);
        }

        return $this->render('admin/system/course-setting.html.twig', array(
            'courseSetting' => $courseSetting,
            'defaultSetting' => $defaultSetting,
            'hasOwnCopyright' => false,
        ));
    }

    public function courseAvatarAction(Request $request)
    {
        $defaultSetting = $this->getSettingService()->get('default', array());

        if ('POST' == $request->getMethod()) {
            $courseDefaultSetting = $request->request->get('defaultCoursePicture', 0);
            $defaultSetting = array_merge($defaultSetting, array('defaultCoursePicture' => $courseDefaultSetting));

            $this->getSettingService()->set('default', $defaultSetting);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('admin_setting_course_avatar'));
        }

        return $this->render('admin/system/course-avatar.html.twig', array(
            'defaultSetting' => $defaultSetting,
            'hasOwnCopyright' => false,
        ));
    }

    public function liveCourseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', array());
        $liveCourseSetting = $this->getSettingService()->get('live-course', array());
        $client = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        $default = array(
            'live_course_enabled' => '0',
        );

        $this->getSettingService()->set('course', $courseSetting);
        $this->getSettingService()->set('live-course', $liveCourseSetting);
        $setting = array_merge($default, $liveCourseSetting);

        if ('POST' == $request->getMethod()) {
            $liveCourseSetting = $request->request->all();
            $liveCourseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];
            $setting = array_merge($courseSetting, $liveCourseSetting);
            $this->getSettingService()->set('live-course', $liveCourseSetting);
            $this->getSettingService()->set('course', $setting);

            $hiddenMenus = $this->getSettingService()->get('menu_hiddens', array());

            if ($liveCourseSetting['live_course_enabled']) {
                unset($hiddenMenus['admin_live_course_add']);
                unset($hiddenMenus['admin_live_course']);
            } else {
                $hiddenMenus['admin_live_course_add'] = true;
                $hiddenMenus['admin_live_course'] = true;
            }

            $this->getSettingService()->set('menu_hiddens', $hiddenMenus);

            $this->getLogService()->info('admin/system/', 'update_settings', '更新课程设置', $setting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $setting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

        return $this->render('admin/system/live-course-setting.html.twig', array(
            'courseSetting' => $setting,
            'capacity' => $capacity,
        ));
    }

    public function questionsSettingAction(Request $request)
    {
        $questionsSetting = $this->getSettingService()->get('questions', array());

        if (empty($questionsSetting)) {
            $default = array(
                'testpaper_answers_show_mode' => 'submitted',
            );
            $questionsSetting = $default;
        }

        if ('POST' == $request->getMethod()) {
            $questionsSetting = $request->request->all();
            $this->getSettingService()->set('questions', $questionsSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/questions-setting.html.twig');
    }

    protected function getCourseDefaultSet()
    {
        $default = array(
            'defaultCoursePicture' => 0,
            'defaultCoursePictureFileName' => 'coursePicture',
            'articleShareContent' => '我正在看{{articletitle}}，关注{{sitename}}，分享知识，成就未来。',
            'courseShareContent' => '我正在学习{{course}}，收获巨大哦，一起来学习吧！',
            'groupShareContent' => '我在{{groupname}}小组,发表了{{threadname}},很不错哦,一起来看看吧!',
            'classroomShareContent' => '我正在学习{{classroom}}，收获巨大哦，一起来学习吧！',
            'chapter_name' => '章',
            'part_name' => '节',
        );

        return $default;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }
}
