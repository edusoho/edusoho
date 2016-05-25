<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class MobileController extends BaseController
{
    public function mobileAction(Request $request)
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

        if ($request->getMethod() == 'POST') {
            $operationMobile = $request->request->all();
            $mobile = array_merge($courseGrids,$settingMobile,$operationMobile);

            $this->getSettingService()->set('operation_mobile', $operationMobile);
            $this->getSettingService()->set('operation_course_grids', $courseGrids);
            $this->getSettingService()->set('mobile', $mobile);
            $this->getLogService()->info('system', 'update_settings', "更新移动客户端设置", $mobile);
            $this->setFlashMessage('success', '移动客户端设置已保存！');
        }

        $bannerCourse1 = ($mobile['bannerJumpToCourseId1'] != " ") ? $this->getCourseService()->getCourse($mobile['bannerJumpToCourseId1']) : null;
        $bannerCourse2 = ($mobile['bannerJumpToCourseId2'] != " ") ? $this->getCourseService()->getCourse($mobile['bannerJumpToCourseId2']) : null;
        $bannerCourse3 = ($mobile['bannerJumpToCourseId3'] != " ") ? $this->getCourseService()->getCourse($mobile['bannerJumpToCourseId3']) : null;
        $bannerCourse4 = ($mobile['bannerJumpToCourseId4'] != " ") ? $this->getCourseService()->getCourse($mobile['bannerJumpToCourseId4']) : null;
        $bannerCourse5 = ($mobile['bannerJumpToCourseId5'] != " ") ? $this->getCourseService()->getCourse($mobile['bannerJumpToCourseId5']) : null;

        return $this->render('TopxiaAdminBundle:System:mobile.html.twig', array(
            'mobile' => $mobile,
            "bannerCourse1" => $bannerCourse1,
            "bannerCourse2" => $bannerCourse2,
            "bannerCourse3" => $bannerCourse3,
            "bannerCourse4" => $bannerCourse4,
            "bannerCourse5" => $bannerCourse5,
        ));
    }

    public function mobileSelectAction(Request $request)
    {
        $operationMobile = $this->getSettingService()->get('operation_mobile', array());
        $courseGrids = $this->getSettingService()->get('operation_course_grids', array());
        $settingMobile = $this->getSettingService()->get('mobile', array());

        $default = array(
            'courseIds' => '', //每周精品课
        );

        $mobile = array_merge($default, $courseGrids);

        if ($request->getMethod() == 'POST') {
            $courseGrids = $request->request->all();

            $mobile = array_merge($operationMobile, $settingMobile, $courseGrids);

            $this->getSettingService()->set('operation_mobile', $operationMobile);
            $this->getSettingService()->set('operation_course_grids', $courseGrids);
            $this->getSettingService()->set('mobile', $mobile);
            $this->getLogService()->info('system', 'update_settings', "更新移动客户端设置", $mobile);
            $this->setFlashMessage('success', '移动客户端设置已保存！');
        }

        $courseIds = explode(",", $mobile['courseIds']);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();
        foreach ($courseIds as $value) {
            if (!empty($value)) {
                $sortedCourses[] = $courses[$value];
            }
        }

        return $this->render('TopxiaAdminBundle:System:course-select.html.twig', array(
            'mobile' => $mobile,
            'courses' => $sortedCourses,
        ));
    }

    public function mobilePictureUploadAction(Request $request, $type)
    {
        $file = $request->files->get($type);
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'mobile_picture' . time() . '.' . $file->getClientOriginalExtension();
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);

        $mobile = $this->getSettingService()->get('mobile', array());
        $mobile[$type] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        $mobile[$type] = ltrim($mobile[$type], '/');

        $this->getSettingService()->set('mobile', $mobile);

        $this->getLogService()->info('system', 'update_settings', "更新网校{$type}图片", array($type => $mobile[$type]));

        $response = array(
            'path' => $mobile[$type],
            'url' => $this->container->get('templating.helper.assets')->getUrl($mobile[$type]),
        );

        return new Response(json_encode($response));
    }

    public function mobilePictureRemoveAction(Request $request, $type)
    {
        $setting = $this->getSettingService()->get("mobile");
        $setting[$type] = '';

        $this->getSettingService()->set('mobile', $setting);

        $this->getLogService()->info('system', 'update_settings', "移除网校{$type}图片");

        return $this->createJsonResponse(true);
    }

    public function customizationUpgradeAction(Request $request)
    {
        $currentVersion = $request->request->get('currentVersion');
        $targetVersion = $request->request->get('targetVersion');

        if (empty($currentVersion) || empty($targetVersion)) {
            throw new \RuntimeException("参数不正确");
        }

        $api = CloudAPIFactory::create('root');

        $resp = $api->post('/customization/mobile/apply', array(
            'currentVersion' => $currentVersion,
            'targetVersion' => $targetVersion,
        ));

        return $this->createJsonResponse($resp);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserFieldService()
    {
        return $this->getServiceKernel()->createService('User.UserFieldService');
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}
