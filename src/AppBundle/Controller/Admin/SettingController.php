<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\JsonToolkit;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserFieldService;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\Service\H5SettingService;

class SettingController extends BaseController
{
    public function postNumRulesAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $setting = $request->request->get('setting', array());
            $this->getSettingService()->set('post_num_rules', $setting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $setting = $this->getSettingService()->get('post_num_rules', array());
        $setting = JsonToolkit::prettyPrint(json_encode($setting));

        return $this->render('admin/system/post-num-rules.html.twig', array(
            'setting' => $setting,
        ));
    }

    public function mobileAction(Request $request)
    {
        $operationMobile = $this->getSettingService()->get('operation_mobile', array());
        $courseGrids = $this->getSettingService()->get('operation_course_grids', array());
        $settingMobile = $this->getSettingService()->get('mobile', array());

        $default = array(
            'enabled' => 1, // 网校状态
            'ver' => 1, //是否是新版
            'about' => '', // 网校简介
            'logo' => '', // 网校Logo
            'appId' => '',
            'appname' => '',
            'appabout' => '',
            'applogo' => '',
            'appcover' => '',
            'notice' => '', //公告
            'splash1' => '', // 启动图1
            'splash2' => '', // 启动图2
            'splash3' => '', // 启动图3
            'splash4' => '', // 启动图4
            'splash5' => '', // 启动图5
            'studyCenter' => array(
                'liveScheduleEnabled' => 0,
                'historyLearningEnabled' => 1,
                'myCacheEnabled' => 1,
                'myQAEnabled' => 1,
            ),
        );

        $mobile = array_merge($default, $settingMobile);

        if ('POST' === $request->getMethod()) {
            $settingMobile = $request->request->all();

            $mobile = array_merge($settingMobile, $operationMobile, $courseGrids);

            $this->getSettingService()->set('operation_mobile', $operationMobile);
            $this->getSettingService()->set('operation_course_grids', $courseGrids);
            if (!empty($mobile['bundleId'])) {
                $mobile['bundleId'] = trim($mobile['bundleId']);
            }

            if (isset($mobile['liveScheduleEnabled'])) {
                $mobile['studyCenter'] = array(
                    'liveScheduleEnabled' => $mobile['liveScheduleEnabled'],
                    'historyLearningEnabled' => 1,
                    'myCacheEnabled' => 1,
                    'myQAEnabled' => 1,
                );
                unset($mobile['liveScheduleEnabled']);
            }

            $this->getSettingService()->set('mobile', $mobile);

            $this->setFlashMessage('success', 'site.save.success');
        }
        try {
            $result = CloudAPIFactory::create('leaf')->get('/me');
        } catch (\Exception $e) {
            return $this->render('admin/system/mobile.setting.error.html.twig');
        }

        $mobileCode = ((array_key_exists('mobileCode', $result) && !empty($result['mobileCode'])) ? $result['mobileCode'] : 'edusohov3');

        //是否拥有定制app
        $hasMobile = isset($result['hasMobile']) ? $result['hasMobile'] : 0;

        return $this->render('admin/system/mobile.setting.html.twig', array(
            'mobile' => $mobile,
            'mobileCode' => $mobileCode,
            'hasMobile' => $hasMobile,
            'appDiscoveryVersion' => $this->getH5SettingService()->getAppDiscoveryVersion(),
        ));
    }

    public function mobileDiscoveriesAction(Request $request)
    {
        $appDiscoveryVersion = $this->getH5SettingService()->getAppDiscoveryVersion();

        if (0 == $appDiscoveryVersion) {
            return $this->redirect($this->generateUrl('admin_setting_mobile'));
        }

        return $this->render('admin/system/mobile.setting.discoveries.html.twig', array());
    }

    public function mobileIapProductAction(Request $request)
    {
        $products = $this->getSettingService()->get('mobile_iap_product', array());
        if ('POST' === $request->getMethod()) {
            $fileds = $request->request->all();

            //新增校验
            if (empty($fileds['productId']) || empty($fileds['title']) || empty($fileds['price']) || !is_numeric($fileds['price'])) {
                $this->setFlashMessage('danger', 'admin.setting.mobile.lap.incorrect_input');

                return $this->redirect($this->generateUrl('admin_setting_mobile_iap_product'));
            }

            //新增
            $products[$fileds['productId']] = array(
                'productId' => $fileds['productId'],
                'title' => $fileds['title'],
                'price' => $fileds['price'],
            );
            $this->getSettingService()->set('mobile_iap_product', $products);

            $this->getLogService()->info('system', 'update_settings', '更新IOS内购产品设置', $products);
            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('admin_setting_mobile_iap_product'));
        }

        return $this->render('admin/system/mobile-iap-product.html.twig', array(
            'products' => $products,
        ));
    }

    public function mobileIapProductDeleteAction(Request $request, $productId)
    {
        $products = $this->getSettingService()->get('mobile_iap_product', array());

        if (array_key_exists($productId, $products)) {
            unset($products[$productId]);
        }

        $this->getSettingService()->set('mobile_iap_product', $products);

        return $this->createJsonResponse(true);
    }

    public function mobilePictureUploadAction(Request $request, $type)
    {
        $fileId = $request->request->get('id');
        $file = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($file)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $filename = 'mobile_picture'.time().'.'.$file->getExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);

        $mobile = $this->getSettingService()->get('mobile', array());
        $mobile[$type] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        $mobile[$type] = ltrim($mobile[$type], '/');

        $this->getSettingService()->set('mobile', $mobile);

        $response = array(
            'path' => $mobile[$type],
            'url' => $this->container->get('assets.packages')->getUrl($mobile[$type]),
        );

        return new Response(json_encode($response));
    }

    public function mobilePictureRemoveAction(Request $request, $type)
    {
        $setting = $this->getSettingService()->get('mobile');
        $setting[$type] = '';

        $this->getSettingService()->set('mobile', $setting);

        return $this->createJsonResponse(true);
    }

    public function logoUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('site', array());

        $oldFileId = empty($site['logo_file_id']) ? null : $site['logo_file_id'];
        $site['logo_file_id'] = $fileId;
        $site['logo'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['logo'] = ltrim($site['logo'], '/');

        $this->getSettingService()->set('site', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $response = array(
            'path' => $site['logo'],
            'url' => $this->container->get('assets.packages')->getUrl($site['logo']),
        );

        return $this->createJsonResponse($response);
    }

    public function logoRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('site');
        $setting['logo'] = '';

        $fileId = empty($setting['logo_file_id']) ? null : $setting['logo_file_id'];
        $setting['logo_file_id'] = '';

        $this->getSettingService()->set('site', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        return $this->createJsonResponse(true);
    }

    public function liveLogoUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('live-course', array());

        $oldFileId = empty($site['logo_file_id']) ? null : $site['logo_file_id'];
        $site['logo_file_id'] = $fileId;
        $site['logo_path'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['logo_path'] = ltrim($site['live_logo'], '/');

        $this->getSettingService()->set('live-course', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        $this->getLogService()->info('system', 'update_settings', '更新直播LOGO', array('live_logo' => $site['logo_path']));

        $response = array(
            'path' => $site['logo_path'],
            'url' => $this->container->get('assets.packages')->getUrl($site['logo_path']),
        );

        return $this->createJsonResponse($response);
    }

    public function faviconUploadAction(Request $request)
    {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);

        if (!FileToolkit::isImageFile($objectFile)) {
            $this->createNewException(FileToolkitException::NOT_IMAGE());
        }

        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file['uri']);

        $site = $this->getSettingService()->get('site', array());

        $oldFileId = empty($site['favicon_file_id']) ? null : $site['favicon_file_id'];
        $site['favicon_file_id'] = $fileId;
        $site['favicon'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/".$parsed['path'];
        $site['favicon'] = ltrim($site['favicon'], '/');

        $this->getSettingService()->set('site', $site);

        if ($oldFileId) {
            $this->getFileService()->deleteFile($oldFileId);
        }

        //浏览器图标覆盖默认图标
        copy($this->getParameter('kernel.root_dir').'/../web/'.$site['favicon'], $this->getParameter('kernel.root_dir').'/../web/favicon.ico');

        $this->getLogService()->info('system', 'update_settings', '更新浏览器图标', array('favicon' => $site['favicon']));

        $response = array(
            'path' => $site['favicon'],
            'url' => $this->container->get('assets.packages')->getUrl($site['favicon']),
        );

        return $this->createJsonResponse($response);
    }

    public function faviconRemoveAction(Request $request)
    {
        $setting = $this->getSettingService()->get('site');
        $setting['favicon'] = '';

        $fileId = empty($setting['favicon_file_id']) ? null : $setting['favicon_file_id'];
        $setting['favicon_file_id'] = '';

        $this->getSettingService()->set('site', $setting);

        if ($fileId) {
            $this->getFileService()->deleteFile($fileId);
        }

        return $this->createJsonResponse(true);
    }

    protected function setCloudSmsKey($key, $val)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        $setting[$key] = $val;
        $this->getSettingService()->set('cloud_sms', $setting);
    }

    public function mailerAction(Request $request)
    {
        if ($this->getWebExtension()->isTrial()) {
            return $this->render('admin/system/mailer.html.twig', array());
        }

        $mailer = $this->getSettingService()->get('mailer', array());
        $default = array(
            'enabled' => 0,
            'host' => '',
            'port' => '',
            'username' => '',
            'password' => '',
            'from' => '',
            'name' => '',
        );
        $mailer = array_merge($default, $mailer);
        if ($request->isMethod('POST')) {
            $mailer = $request->request->all();
            $this->getSettingService()->set('mailer', $mailer);
            $mailerWithoutPassword = $mailer;
            $mailerWithoutPassword['password'] = '******';
            $this->setFlashMessage('success', 'site.save.success');
        }

        $status = $this->checkMailerStatus();

        $cloudMailName = '';

        return $this->render('admin/system/mailer.html.twig', array(
            'mailer' => $mailer,
            'status' => $status,
            'cloudMailName' => $cloudMailName,
        ));
    }

    /*
     * 当前云邮件字段为cloud_email_crm
     */
    protected function checkMailerStatus()
    {
        $cloudEmail = $this->getSettingService()->get('cloud_email_crm', array());
        $mailer = $this->getSettingService()->get('mailer', array());

        if (!empty($cloudEmail) && 'enable' === $cloudEmail['status']) {
            return 'cloud_email_crm';
        }

        if (!empty($mailer) && 1 == $mailer['enabled']) {
            return 'email';
        }

        return '';
    }

    public function mailerTestAction(Request $request)
    {
        $user = $this->getUser();
        $mailOptions = array(
            'to' => $user['email'],
            'template' => 'email_system_self_test',
        );
        $mailFactory = $this->getBiz()->offsetGet('mail_factory');
        $mail = $mailFactory($mailOptions);

        try {
            $mail->send();

            return $this->createJsonResponse(array(
                'status' => true,
            ));
        } catch (\Exception $e) {
            return $this->createJsonResponse(array(
                'status' => false,
                'message' => $e->getMessage(),
            ));
        }
    }

    public function defaultAction(Request $request)
    {
        $defaultSetting = $this->getSettingService()->get('default', array());
        $path = $this->container->getParameter('kernel.root_dir').'/../web/assets/img/default/';

        $default = $this->getDefaultSet();

        $defaultSetting = array_merge($default, $defaultSetting);

        if ('POST' === $request->getMethod()) {
            $defaultSetting = $request->request->all();

            if (!isset($defaultSetting['user_name'])) {
                $defaultSetting['user_name'] = '学员';
            }

            if (!isset($defaultSetting['chapter_name'])) {
                $defaultSetting['chapter_name'] = '章';
            }

            if (!isset($defaultSetting['part_name'])) {
                $defaultSetting['part_name'] = '节';
            }

            $default = $this->getSettingService()->get('default', array());
            $defaultSetting = array_merge($default, $defaultSetting);

            $this->getSettingService()->set('default', $defaultSetting);
            $this->getLogService()->info('system', 'update_settings', '更新系统默认设置', $defaultSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/default.html.twig', array(
            'defaultSetting' => $defaultSetting,
            'hasOwnCopyright' => false,
        ));
    }

    protected function getDefaultSet()
    {
        $default = array(
            'defaultAvatar' => 0,
            'defaultCoursePicture' => 0,
            'defaultAvatarFileName' => 'avatar',
            'defaultCoursePictureFileName' => 'coursePicture',
            'articleShareContent' => '我正在看{{articletitle}}，关注{{sitename}}，分享知识，成就未来。',
            'courseShareContent' => '我正在学习{{course}}，收获巨大哦，一起来学习吧！',
            'groupShareContent' => '我在{{groupname}}小组，看{{threadname}}，很不错哦，一起来看看吧！',
            'classroomShareContent' => '我正在学习{{classroom}}，收获巨大哦，一起来学习吧！',
            'user_name' => '学员',
            'chapter_name' => '章',
            'part_name' => '节',
        );

        return $default;
    }

    public function ipBlacklistAction(Request $request)
    {
        $settingService = $this->getSettingService();

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();

            $purifiedBlackIps = trim(str_replace(array("\r\n", "\n", "\r"), ' ', $data['blackListIps']));
            $purifiedWhiteIps = isset($data['whiteListIps']) ? $data['whiteListIps'] : null;
            $purifiedWhiteIps = trim(str_replace(array("\r\n", "\n", "\r"), ' ', $purifiedWhiteIps));

            $logService = $this->getLogService();

            if (empty($purifiedBlackIps)) {
                $settingService->delete('blacklist_ip');

                $blackListIps['ips'] = array();
            } else {
                $blackListIps['ips'] = array_filter(explode(' ', $purifiedBlackIps));
                $settingService->set('blacklist_ip', $blackListIps);
            }

            if (empty($purifiedWhiteIps)) {
                $settingService->delete('whitelist_ip');

                $whiteListIps['ips'] = array();
            } else {
                $whiteListIps['ips'] = array_filter(explode(' ', $purifiedWhiteIps));
                $settingService->set('whitelist_ip', $whiteListIps);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        $blackListIps = $settingService->get('blacklist_ip', array());
        $whiteListIps = $settingService->get('whitelist_ip', array());

        if (!empty($blackListIps)) {
            $default['ips'] = join("\n", $blackListIps['ips']);
            $blackListIps = array_merge($blackListIps, $default);
        } else {
            $blackListIps = array();
        }

        if (!empty($whiteListIps)) {
            $default['ips'] = join("\n", $whiteListIps['ips']);
            $whiteListIps = array_merge($whiteListIps, $default);
        } else {
            $whiteListIps = array();
        }

        return $this->render('admin/system/ip-blacklist.html.twig', array(
            'blackListIps' => $blackListIps,
            'whiteListIps' => $whiteListIps,
        ));
    }

    public function customerServiceAction(Request $request)
    {
        $customerServiceSetting = $this->getSettingService()->get('customerService', array());

        $default = array(
            'customer_service_mode' => 'closed',
            'customer_of_qq' => '',
            'customer_of_mail' => '',
            'customer_of_phone' => '',
        );

        $customerServiceSetting = array_merge($default, $customerServiceSetting);

        if ('POST' === $request->getMethod()) {
            $customerServiceSetting = $request->request->all();
            $this->getSettingService()->set('customerService', $customerServiceSetting);
            $this->getLogService()->info('system', 'customerServiceSetting', '客服管理设置', $customerServiceSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/customer-service.html.twig', array(
            'customerServiceSetting' => $customerServiceSetting,
        ));
    }

    public function courseSettingAction(Request $request)
    {
        $courseSetting = $this->getSettingService()->get('course', array());

        $client = new EdusohoLiveClient();
        $capacity = $client->getCapacity();

        $default = array(
            'welcome_message_enabled' => '0',
            'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
            'buy_fill_userinfo' => '0',
            'teacher_manage_marketing' => '1',
            'teacher_search_order' => '0',
            'teacher_manage_student' => '0',
            'teacher_export_student' => '0',
            'free_course_nologin_view' => '1',
            'relatedCourses' => '0',
            'coursesPrice' => '0',
            'allowAnonymousPreview' => '1',
            'live_course_enabled' => '0',
            'userinfoFields' => array(),
            'userinfoFieldNameArray' => array(),
            'copy_enabled' => '0',
        );

        $this->getSettingService()->set('course', $courseSetting);
        $courseSetting = array_merge($default, $courseSetting);

        if ('POST' === $request->getMethod()) {
            $courseSetting = $request->request->all();

            if (!isset($courseSetting['userinfoFields'])) {
                $courseSetting['userinfoFields'] = array();
            }

            if (!isset($courseSetting['userinfoFieldNameArray'])) {
                $courseSetting['userinfoFieldNameArray'] = array();
            }

            $courseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

            $this->getSettingService()->set('course', $courseSetting);
            $this->getLogService()->info('system', 'update_settings', '更新课程设置', $courseSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $courseSetting['live_student_capacity'] = empty($capacity['capacity']) ? 0 : $capacity['capacity'];

        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        if ($courseSetting['userinfoFieldNameArray']) {
            foreach ($userFields as $key => $fieldValue) {
                if (!in_array($fieldValue['fieldName'], $courseSetting['userinfoFieldNameArray'])) {
                    $courseSetting['userinfoFieldNameArray'][] = $fieldValue['fieldName'];
                }
            }
        }

        return $this->render('admin/system/course-setting.html.twig', array(
            'courseSetting' => $courseSetting,
            'capacity' => $capacity,
            'userFields' => $userFields,
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

        if ('POST' === $request->getMethod()) {
            $questionsSetting = $request->request->all();
            $this->getSettingService()->set('questions', $questionsSetting);
            $this->getLogService()->info('system', 'questions_settings', '更新题库设置', $questionsSetting);
            $this->setFlashMessage('success', 'site.save.success');
        }

        return $this->render('admin/system/questions-setting.html.twig');
    }

    public function adminSyncAction(Request $request)
    {
        $currentUser = $this->getUser();
        $setting = $this->getSettingService()->get('user_partner', array());

        if (empty($setting['mode']) || !in_array($setting['mode'], array('phpwind', 'discuz'))) {
            return $this->createMessageResponse('info', '未开启用户中心，不能同步管理员帐号！');
        }

        $bind = $this->getUserService()->getUserBindByTypeAndUserId($setting['mode'], $currentUser['id']);

        if ($bind) {
            goto response;
        } else {
            $bind = null;
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $partnerUser = $this->getAuthService()->checkPartnerLoginByNickname($data['nickname'], $data['password']);

            if (empty($partnerUser)) {
                $this->setFlashMessage('danger', 'site.incorrect.username_or_password');
                goto response;
            } else {
                $this->getUserService()->changeEmail($currentUser['id'], $partnerUser['email']);
                $this->getUserService()->changeNickname($currentUser['id'], $partnerUser['nickname']);
                $this->getUserService()->changePassword($currentUser['id'], $data['password']);
                $this->getUserService()->bindUser($setting['mode'], $partnerUser['id'], $currentUser['id'], null);
                $user = $this->getUserService()->getUser($currentUser['id']);
                $this->authenticateUser($user);

                $this->setFlashMessage('success', 'site.save.success');

                return $this->redirect($this->generateUrl('admin_setting_user_center'));
            }
        }

        response:
        return $this->render('admin/system/admin-sync.html.twig', array(
            'mode' => $setting['mode'],
            'bind' => $bind,
        ));
    }

    public function performanceAction(Request $request)
    {
        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $this->setFlashMessage('success', 'site.save.success');
            $this->getSettingService()->set('performance', $data);

            return $this->redirect($this->generateUrl('admin_performance'));
        }

        return $this->render('admin/system/performance-setting.html.twig');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
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

    /**
     * @return H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }
}
