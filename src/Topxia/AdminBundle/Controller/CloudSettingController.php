<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

use Topxia\Service\Util\LiveClientFactory;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Util\PluginUtil;
use Topxia\Service\Util\CloudClientFactory;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class CloudSettingController extends BaseController
{

    public function indexAction(Request $request)
    {
        $storageSetting = $this->getSettingService()->get('storage', array());
        $default = array(
            'upload_mode' => 'local',
            'cloud_access_key' => '',
            'cloud_secret_key' => '',
            'cloud_bucket' => '',
            'cloud_api_server' => '',
            'video_quality' => 'low',
            'video_audio_quality' => 'low',
            'video_watermark' => 0,
            'video_watermark_image' => '',
            'video_embed_watermark_image' => '',
            'video_watermark_position' => 'topright',
            'video_fingerprint' => 0,
            'video_header' => null,
        );

        $storageSetting = array_merge($default, $storageSetting);
        if ($request->getMethod() == 'POST') {
            $storageSetting = $request->request->all();
            $this->getSettingService()->set('storage', $storageSetting);

            if (!empty($storageSetting['cloud_access_key']) or !empty($storageSetting['cloud_secret_key'])) {
                if (!empty($storageSetting['cloud_access_key']) and !empty($storageSetting['cloud_secret_key'])) {
                    $factory = new CloudClientFactory();
                    $client = $factory->createClient($storageSetting);
                    $keyCheckResult = $client->checkKey();
                } else {
                    $keyCheckResult = array('error' => 'error');
                }
            } else {
                $keyCheckResult = array('status' => 'ok');
            }

            $cop = $this->getAppService()->checkAppCop();
            if ($cop && isset($cop['cop']) && ($cop['cop'] == 1)) {
                $this->getSettingService()->set('_app_cop', 1);
            } else {
                $this->getSettingService()->set('_app_cop', 0);
            }
            PluginUtil::refresh();
            $this->getLogService()->info('system', 'update_settings', "更新云平台设置", $storageSetting);
            if (!empty($keyCheckResult['status']) && $keyCheckResult['status'] == 'ok') {
                $this->setFlashMessage('success', '云平台设置已保存！');
            } else {
                $this->setFlashMessage('danger', 'AccessKey或者SecretKey设置不正确，会影响到系统正常的运行，请修改设置。');
            }
        }

        $headLeader = array();
        if(!empty($storageSetting) && array_key_exists("video_header", $storageSetting) && $storageSetting["video_header"]){
            $headLeader = $this->getUploadFileService()->getFileByTargetType('headLeader');
        }

        return $this->render('TopxiaAdminBundle:CloudSetting:index.html.twig', array(
            'storageSetting'=>$storageSetting,
            'headLeader'=>$headLeader
        ));
    }

    public function videoWatermarkUploadAction(Request $request)
    {
        $file = $request->files->get('watermark');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'watermark_' . time() . '.' . $file->getClientOriginalExtension();
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);
        $path = "system/{$filename}";

        $response = array(
            'path' => $path,
            'url' =>  $this->get('topxia.twig.web_extension')->getFileUrl($path),
        );

        return new Response(json_encode($response));
    }


   public function videoEmbedWatermarkUploadAction(Request $request)
    {
        $file = $request->files->get('watermark');
        if (!FileToolkit::isImageFile($file)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }

        $filename = 'watermarkembed_' . time() . '.' . $file->getClientOriginalExtension();
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
        $file = $file->move($directory, $filename);
        $path = "system/{$filename}";
        $originFileInfo = getimagesize($file);
        $filePath = $this->container->getParameter('topxia.upload.public_directory')."/".$path;
        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);

        $pathinfo = pathinfo($filePath);
        $specification['240'] = 20;
        $specification['360'] = 30;
        $specification['480'] = 40;
        $specification['720'] = 60;
        $specification['1080'] = 90;

        foreach ($specification as $key => $value) {
            $width= ($originFileInfo[0]*$value/$originFileInfo[1]);
            $specialImage = $rawImage->copy();
            $specialImage->resize(new Box($width, $value));
            $filePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$key}.{$pathinfo['extension']}";
            $specialImage->save($filePath);
        }

        $response = array(
            'path' => $path,
            'url' =>  $this->get('topxia.twig.web_extension')->getFileUrl($path),
        );

        return new Response(json_encode($response));
    }


    public function videoWatermarkRemoveAction(Request $request)
    {
        return $this->createJsonResponse(true);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }
}