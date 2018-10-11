<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        return $this->render('admin/system/mobile.html.twig', array(
            'mobile' => $mobile,
            'bannerCourses' => $bannerCourses,
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

        if ('POST' == $request->getMethod()) {
            $courseGrids = $request->request->all();

            $mobile = array_merge($operationMobile, $settingMobile, $courseGrids);

            $this->getSettingService()->set('operation_mobile', $operationMobile);
            $this->getSettingService()->set('operation_course_grids', $courseGrids);
            $this->getSettingService()->set('mobile', $mobile);
            $this->setFlashMessage('success', 'site.save.success');
        }

        $courseIds = explode(',', $mobile['courseIds']);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();
        foreach ($courseIds as $value) {
            if (!empty($value) && !empty($courses[$value])) {
                $sortedCourses[] = $courses[$value];
            }
        }

        return $this->render('admin/system/course-select.html.twig', array(
            'mobile' => $mobile,
            'courses' => $sortedCourses,
        ));
    }

    public function mobilePictureUploadAction(Request $request, $type)
    {
        $fileId = $request->request->get('id');
        $file = $this->getFileService()->getFileObject($fileId);
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
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
            'url' => $this->container->get('templating.helper.assets')->getUrl($mobile[$type]),
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

    public function customizationUpgradeAction(Request $request)
    {
        $currentVersion = $request->request->get('currentVersion');
        $targetVersion = $request->request->get('targetVersion');

        if (empty($currentVersion) || empty($targetVersion)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
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

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
