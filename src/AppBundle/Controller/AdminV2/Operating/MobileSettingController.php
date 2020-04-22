<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\Content\Service\FileService;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\User\Service\AuthService;
use Biz\User\Service\UserFieldService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Biz\CloudPlatform\CloudAPIFactory;

class MobileSettingController extends BaseController
{
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
            return $this->render('admin-v2/operating/mobile-setting/mobile.setting.error.html.twig');
        }

        $mobileCode = ((array_key_exists('mobileCode', $result) && !empty($result['mobileCode'])) ? $result['mobileCode'] : 'edusohov3');

        //是否拥有定制app
        $hasMobile = isset($result['hasMobile']) ? $result['hasMobile'] : 0;

        return $this->render('admin-v2/operating/mobile-setting/mobile.setting.html.twig', array(
            'mobile' => $mobile,
            'mobileCode' => $mobileCode,
            'hasMobile' => $hasMobile,
        ));
    }

    public function bannerAction(Request $request)
    {
        $operationMobile = $this->getSettingService()->get('operation_mobile', array());
        $courseGrids = $this->getSettingService()->get('operation_course_grids', array());
        $settingMobile = $this->getSettingService()->get('mobile', array());

        $default = array(
            'banner1' => '', // 轮播图1
            'banner2' => '', // 轮播图2
            'banner3' => '', // 轮播图3
            'banner4' => '', // 轮播图4
            'banner5' => '', // 轮播图5
            'bannerUrl1' => '', // 轮播图1的触发地址
            'bannerUrl2' => '', // 轮播图2的触发地址
            'bannerUrl3' => '', // 轮播图3的触发地址
            'bannerUrl4' => '', // 轮播图4的触发地址
            'bannerUrl5' => '', // 轮播图5的触发地址
            'bannerClick1' => '', // 轮播图1是否触发动作
            'bannerClick2' => '', // 轮播图2是否触发动作
            'bannerClick3' => '', // 轮播图3是否触发动作
            'bannerClick4' => '', // 轮播图4是否触发动作
            'bannerClick5' => '', // 轮播图5是否触发动作
            'bannerJumpToCourseId1' => ' ',
            'bannerJumpToCourseId2' => ' ',
            'bannerJumpToCourseId3' => ' ',
            'bannerJumpToCourseId4' => ' ',
            'bannerJumpToCourseId5' => ' ',
        );

        $mobile = array_merge($default, $operationMobile);

        if ('POST' == $request->getMethod()) {
            $operationMobile = $request->request->all();
            $mobile = array_merge($courseGrids, $settingMobile, $operationMobile);

            $this->getSettingService()->set('operation_mobile', $operationMobile);
            $this->getSettingService()->set('operation_course_grids', $courseGrids);
            $this->getSettingService()->set('mobile', $mobile);
            $this->setFlashMessage('success', 'site.save.success');
        }
        $bannerCourses = array();
        for ($i = 1; $i <= 5; ++$i) {
            $bannerCourses[$i] = (' ' != $mobile['bannerJumpToCourseId'.$i]) ? $this->getCourseService()->getCourse($mobile['bannerJumpToCourseId'.$i]) : null;
        }

        return $this->render('admin-v2/operating/mobile-setting/banner.html.twig', array(
            'mobile' => $mobile,
            'bannerCourses' => $bannerCourses,
        ));
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

    public function mobileDiscoveriesAction(Request $request)
    {
        return $this->render('admin-v2/operating/mobile-setting/mobile.setting.discoveries.html.twig', array());
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
}
