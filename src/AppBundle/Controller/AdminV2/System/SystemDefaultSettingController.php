<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Common\ArrayToolkit;
use Biz\Content\Service\FileService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AdminV2\BaseController;

class SystemDefaultSettingController extends BaseController
{
    public function defaultAvatarCropAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();
            $data = $options['images'];

            $fileIds = ArrayToolkit::column($data, 'id');
            $files = $this->getFileService()->getFilesByIds($fileIds);

            $files = ArrayToolkit::index($files, 'id');
            $fileIds = ArrayToolkit::index($data, 'type');

            $setting = $this->getSettingService()->get('default', array());

            $oldAvatars = array(
                'avatar.png' => !empty($setting['avatar.png']) ? $setting['avatar.png'] : null,
            );

            $setting['defaultAvatar'] = 1;
            unset($setting['defaultAvatarFileName']);
            $setting['avatar.png'] = $files[$fileIds['avatar.png']['id']]['uri'];
            $this->getSettingService()->set('default', $setting);

            $fileService = $this->getFileService();
            array_map(function ($oldAvatar) use ($fileService) {
                if (!empty($oldAvatar)) {
                    $fileService->deleteFileByUri($oldAvatar);
                }
            }, $oldAvatars);

            return $this->redirect($this->generateUrl('admin_v2_setting_avatar'));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('admin-v2/system/user-setting/default-avatar-crop.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function defaultCoursePictureCropAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();
            $data = $options['images'];

            $fileIds = ArrayToolkit::column($data, 'id');
            $files = $this->getFileService()->getFilesByIds($fileIds);

            $files = ArrayToolkit::index($files, 'id');
            $fileIds = ArrayToolkit::index($data, 'type');

            $setting = $this->getSettingService()->get('default', array());

            $oldAvatars = array(
                'course.png' => !empty($setting['course.png']) ? $setting['course.png'] : null,
            );

            $setting['defaultCoursePicture'] = 1;
            unset($setting['defaultCoursePictureFileName']);
            $setting['course.png'] = $files[$fileIds['course.png']['id']]['uri'];

            $this->getSettingService()->set('default', $setting);

            $fileService = $this->getFileService();
            array_map(function ($oldAvatar) use ($fileService) {
                if (!empty($oldAvatar)) {
                    $fileService->deleteFileByUri($oldAvatar);
                }
            }, $oldAvatars);

            return $this->redirect($this->generateUrl('admin_v2_setting_course_avatar'));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render('admin-v2/system/course-setting/default-course-picture-crop.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
