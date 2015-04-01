<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Util\PluginUtil;
use Topxia\Service\Util\CloudClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class SystemDefaultSettingController extends BaseController
{
    public function defaultAvatarCropAction(Request $request)
    {
        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $data = $options["images"];

            $fileIds = ArrayToolkit::column($data, "id");
            $files = $this->getFileService()->getFilesByIds($fileIds);

            $files = ArrayToolkit::index($files, "id");
            $fileIds = ArrayToolkit::index($data, "type");

            $setting = $this->getSettingService()->get("default",array());

            $oldAvatars = array(
                'smallAvatar' => !empty($setting['smallDefaultAvatarUri']) ? $setting['smallDefaultAvatarUri'] : null,
                'smallAvatar' => !empty($setting['smallDefaultAvatarUri']) ? $setting['smallDefaultAvatarUri'] : null,
                'largeAvatar' => !empty($setting['largeDefaultAvatarUri']) ? $setting['largeDefaultAvatarUri'] : null,
            );
            
            $setting['defaultAvatar'] = 1;
            unset($setting['defaultAvatarFileName']);
            $setting['smallDefaultAvatarUri'] = $files[$fileIds["small"]["id"]]["uri"];
            $setting['largeDefaultAvatarUri'] = $files[$fileIds["large"]["id"]]["uri"];

            $this->getSettingService()->set("default",$setting);

            array_map(function($oldAvatar){
                if (!empty($oldAvatar)) {
                    $this->getFileService()->deleteFileByUri($oldAvatar);
                }
            }, $oldAvatars);

            return $this->redirect($this->generateUrl('admin_setting_default'));
        }

        $fileId = $request->getSession()->get("fileId");
        if(empty($fileId)) {
            return $this->createMessageResponse("error", "参数不正确");
        }

        $file = $this->getFileService()->getFile($fileId);
        if(empty($file)) {
            return $this->createMessageResponse("error", "文件不存在");
        }
        
        $parsed = $this->getFileService()->parseFileUri($file["uri"]);

        list($naturalSize, $scaledSize) = FileToolkit::getImgInfo($parsed['fullpath'], 270, 270);

        return $this->render('TopxiaAdminBundle:System:default-avatar-crop.html.twig',array(
            'pictureUrl' => $parsed["path"],
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function defaultCoursePictureCropAction(Request $request)
    {   
        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $data = $options["images"];

            $fileIds = ArrayToolkit::column($data, "id");
            $files = $this->getFileService()->getFilesByIds($fileIds);

            $files = ArrayToolkit::index($files, "id");
            $fileIds = ArrayToolkit::index($data, "type");

            $setting = $this->getSettingService()->get("default",array());

            $oldAvatars = array(
                'smallCoursePicture' => !empty($setting['smallDefaultCoursePictureUri']) ? $setting['smallDefaultCoursePictureUri'] : null,
                'middleCoursePicture' => !empty($setting['middleDefaultCoursePictureUri']) ? $setting['middleDefaultCoursePictureUri'] : null,
                'largeCoursePicture' => !empty($setting['largeDefaultCoursePictureUri']) ? $setting['largeDefaultCoursePictureUri'] : null,
            );
            
            $setting['defaultCoursePicture'] = 1;
            unset($setting['defaultCoursePictureFileName']);
            $setting['smallDefaultCoursePictureUri'] = $files[$fileIds["small"]["id"]]["uri"];
            $setting['middleDefaultCoursePictureUri'] = $files[$fileIds["middle"]["id"]]["uri"];
            $setting['largeDefaultCoursePictureUri'] = $files[$fileIds["large"]["id"]]["uri"];

            $this->getSettingService()->set("default",$setting);

            array_map(function($oldAvatar){
                if (!empty($oldAvatar)) {
                    $this->getFileService()->deleteFileByUri($oldAvatar);
                }
            }, $oldAvatars);

            return $this->redirect($this->generateUrl('admin_setting_default'));
        }

        $fileId = $request->getSession()->get("fileId");
        if(empty($fileId)) {
            return $this->createMessageResponse("error", "参数不正确");
        }

        $file = $this->getFileService()->getFile($fileId);
        if(empty($file)) {
            return $this->createMessageResponse("error", "文件不存在");
        }
        
        $parsed = $this->getFileService()->parseFileUri($file["uri"]);

        list($naturalSize, $scaledSize) = FileToolkit::getImgInfo($parsed['fullpath'], 270, 270);

        return $this->render('TopxiaAdminBundle:System:default-course-picture-crop.html.twig',array(
            'pictureUrl' => $parsed["path"],
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }
}