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
    public function defaultAvatarAction(Request $request)
    {
        $file = $request->files->get('picture');
        if (!FileToolkit::isImageFile($file)) {
            return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
        }

        $filenamePrefix = "avatar";
        $hash = substr(md5($filenamePrefix . time()), -8);
        $ext = $file->getClientOriginalExtension();
        $filename = $filenamePrefix . $hash . '.' . $ext;

        $defaultSetting = $this->getSettingService()->get('default', array());
        $defaultSetting['defaultAvatarFileName'] = $filename;
        $this->getSettingService()->set("default", $defaultSetting);

        $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
        $file = $file->move($directory, $filename);

        $pictureFilePath = $directory.'/'.$filename;

        $imagine = new Imagine();
        $image = $imagine->open($pictureFilePath);

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);

        // @todo fix it.
        $assets = $this->container->get('templating.helper.assets');
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;
        $pictureUrl = ltrim($pictureUrl, ' /');
        $pictureUrl = $assets->getUrl($pictureUrl);

        return $this->render('TopxiaAdminBundle:System:default-avatar-crop.html.twig',array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function defaultAvatarCropAction(Request $request)
    {
        $options = $request->request->all();

        $setting = $this->getSettingService()->get("default",array());
        $setting['defaultAvatar'] = 1;
        $this->getSettingService()->set("default",$setting);
        $filename = $setting['defaultAvatarFileName'];
        $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
        $path = $this->container->getParameter('kernel.root_dir').'/../web/assets/img/default/';

        $pictureFilePath = $directory.'/'.$filename;
        $pathinfo = pathinfo($pictureFilePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($pictureFilePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(220, 220));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));

        $this->filesystem = new Filesystem();
        $this->filesystem->copy($largeFilePath, $path.'large'.$filename);

        $smallImage = $largeImage->copy();
        $smallImage->resize(new Box(120, 120));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $smallImage->save($smallFilePath, array('quality' => 90));

        $this->filesystem->copy($smallFilePath, $path.$filename);

        return $this->redirect($this->generateUrl('admin_setting_default'));
    }

    public function defaultCoursePictureAction(Request $request)
    {
        $file = $request->files->get('picture');
        if (!FileToolkit::isImageFile($file)) {
            return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
        }

        $filenamePrefix = "coursePicture";
        $hash = substr(md5($filenamePrefix . time()), -8);
        $ext = $file->getClientOriginalExtension();
        $filename = $filenamePrefix . $hash . '.' . $ext;

        $defaultSetting = $this->getSettingService()->get('default', array());
        $defaultSetting['defaultCoursePictureFileName'] = $filename;
        $this->getSettingService()->set("default", $defaultSetting);

        $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
        $file = $file->move($directory, $filename);

        $pictureFilePath = $directory.'/'.$filename;

        $imagine = new Imagine();
        $image = $imagine->open($pictureFilePath);

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(480)->heighten(270);

        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return $this->render('TopxiaAdminBundle:System:default-course-picture-crop.html.twig',array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    public function defaultCoursePictureCropAction(Request $request)
    {
        $options = $request->request->all();

        $setting = $this->getSettingService()->get("default",array());
        $setting['defaultCoursePicture'] = 1;
        $this->getSettingService()->set("default",$setting);
        $filename = $setting['defaultCoursePictureFileName'];

        $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
        $path = $this->container->getParameter('kernel.root_dir').'/../web/assets/img/default/';

        $pictureFilePath = $directory.'/'.$filename;
        $pathinfo = pathinfo($pictureFilePath);

        $imagine = new Imagine();
        $rawImage = $imagine->open($pictureFilePath);

        $largeImage = $rawImage->copy();
        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(480, 270));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));

        $this->filesystem = new Filesystem();
        $this->filesystem->copy($largeFilePath, $path.'large'.$filename);

        $smallImage = $largeImage->copy();
        $smallImage->resize(new Box(475,250));
        $smallFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_small.{$pathinfo['extension']}";
        $smallImage->save($smallFilePath, array('quality' => 90));

        $this->filesystem->copy($smallFilePath, $path.$filename);

        return $this->redirect($this->generateUrl('admin_setting_default'));
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}