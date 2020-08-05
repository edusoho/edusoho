<?php

namespace Biz\CloudPlatform\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Service\BaseFacade;
use Biz\CloudPlatform\Service\ResourceFacadeService;
use Biz\S2B2C\Service\FileSourceService;
use Biz\S2B2C\Service\S2B2CFacadeService;

class ResourceFacadeServiceImpl extends BaseFacade implements ResourceFacadeService
{
    public function getPlayerContext($file, $userAgent = '')
    {
        $context = [];

        $context['agentInWhiteList'] = $this->agentInWhiteList($userAgent);

        //对不同的资源类型，添加不同的配置参数
        $method = 'prepare'.ucfirst($file['type']).'Context';
        $context = $this->$method($file, $context);

        //获取用于权限验证的token和资源编码
        $payload = [];
        if (!$this->isHiddenVideoHeader()) {
            // 加入片头信息
            $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
            if (!empty($videoHeaderFile) && 'success' == $videoHeaderFile['convertStatus']) {
                $payload['head'] = $videoHeaderFile['globalId'];
            }
        }
        $context['token'] = $this->makePlayToken($file, 600, $payload);
        $context['resNo'] = $file['globalId'];

        //转码状态
        $context['isFinishConvert'] = 'cloud' != $file['storage'] || !in_array($file['type'], ['ppt', 'document', 'video']) || 'success' == $file['convertStatus'];

        return $context;
    }

    protected function prepareVideoContext($file, $context)
    {
        //微网校用于是否支持 mobile 端判断
        $context['supportMobile'] = intval($this->getSettingService()->node('storage.support_mobile', 0));
        if ('cloud' == $file['storage']) {
            $context['jsPlayer'] = 'balloon-cloud-video-player';
        } else {
            $context['jsPlayer'] = 'local-video-player';
        }

        //是否开启加密增强
        $storageSetting = $this->getSettingService()->get('storage');
        $context['isEncryptionPlus'] = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['enable_hls_encryption_plus'];

        return $context;
    }

    protected function preparePptContext($file, $context)
    {
        return $context;
    }

    protected function prepareAudioContext($file, $context)
    {
        $context['jsPlayer'] = 'audio-player';

        return $context;
    }

    protected function prepareDocumentContext($file, $context)
    {
        return $context;
    }

    public function makePlayToken($file, $lifetime = 600, $payload = [])
    {
        if ('supplier' == $file['storage']) {
            $fileInfo = $this->getS2B2CFileSourceService()->getFullFileInfo($file);

            return $this->getS2B2CFacadeService()->getS2B2CService()->getProductResourceJWTPlayToken($fileInfo['globalId'], $lifetime, $payload);
        }

        return $this->biz['ESCloudSdk.play']->makePlayToken($file['globalId'], $lifetime, $payload);
    }

    public function agentInWhiteList($userAgent)
    {
        $whiteList = ['iPhone', 'iPad', 'Android', 'HTC', 'com.tencent.mm.app.Application'];

        return ArrayToolkit::some($whiteList, function ($agent) use ($userAgent) {
            return strpos($userAgent, $agent) > -1;
        });
    }

    public function getFrontPlaySDKPathByType($type)
    {
        $cdnHost = $this->getSettingService()->node('developer.cloud_sdk_cdn') ?: 'service-cdn.qiqiuyun.net';

        //to do：所有类型的sdkPath要合并成一个
        $paths = [
            'player' => 'js-sdk/sdk-v1.js',
            'newPlayer' => 'js-sdk/sdk-v2.js',
            'audio' => 'js-sdk-v2/sdk-v1.js',
            'video' => 'js-sdk-v2/sdk-v1.js',
            'uploader' => 'js-sdk-v2/uploader/sdk-2.1.0.js',
            'old_uploader' => 'js-sdk/uploader/sdk-v1.js',
            'old_document' => 'js-sdk/document-player/v7/viewer.html',
            'faq' => 'js-sdk/faq/sdk-v1.js',
            'resource_player' => 'js-sdk-v2/sdk-v1.js',
        ];

        if (isset($paths[$type])) {
            $path = $paths[$type];
        } else {
            $path = $type;
        }

        $timestamp = round(time() / 100);

        return '//'.trim($cdnHost, "\/").'/'.$path.'?'.$timestamp;
    }

    public function startUpload($file)
    {
        $params = [
            'extno' => $file['id'],
            'bucket' => $file['bucket'],
            'reskey' => $file['hashId'],
            'name' => $file['name'],
            'size' => $file['fileSize'],
        ];

        if ('attachment' == $file['targetType']) {
            $params['type'] = $file['targetType'];
        }

        if ('subtitle' == $file['targetType']) {
            $params['type'] = 'sub';
        }

        if (isset($file['directives'])) {
            $params['directives'] = $file['directives'];
        }

        if ('video' == $file['type']) {
            $watermarks = $this->getVideoWatermarkImages();

            if (!empty($watermarks)) {
                $params['directives']['watermarks'] = $watermarks;
            }
        }

        if ('audio' == $file['type']) {
            $params['directives']['output'] = 'audio';
            $params['directives']['transcode'] = false;
        }

        if ('ppt' == $file['type']) {
            $params['directives'] = array_merge($params['directives'], ['convertAll' => true]);
        }

        return $this->getResourceService()->startUpload($params);
    }

    public function resumeUpload($params, $file)
    {
        $params = [
            'extno' => $file['id'],
            'bucket' => $params['bucket'],
            'size' => $params['fileSize'],
            'name' => $params['name'],
            'resumeNo' => $file['globalId'],
        ];

        return $this->getResourceService()->startUpload($params);
    }

    public function finishUpload($globalId)
    {
        return $this->getResourceService()->finishUpload($globalId);
    }

    public function getResource($globalId)
    {
        return $this->getResourceService()->get($globalId);
    }

    protected function isHiddenVideoHeader($isHidden = false)
    {
        $storage = $this->getSettingService()->get('storage');
        if (!empty($storage) && array_key_exists('video_header', $storage) && $storage['video_header'] && !$isHidden) {
            return false;
        }

        return true;
    }

    protected function getVideoWatermarkImages()
    {
        $setting = $this->getSettingService()->get('storage', []);

        if (empty($setting['video_embed_watermark_image']) || (2 != $setting['video_watermark'])) {
            return [];
        }

        $videoWatermarkImage = $this->biz['env']['base_url'].$this->biz['topxia.upload.public_url_path'].'/'.$setting['video_embed_watermark_image'];
        $pathinfo = pathinfo($videoWatermarkImage);

        $images = [];
        $heighs = ['240', '360', '480', '720', '1080'];

        foreach ($heighs as $height) {
            $images[$height] = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$height}.{$pathinfo['extension']}";
        }

        return $images;
    }

    protected function getResourceService()
    {
        return $this->biz['ESCloudSdk.resource'];
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    /**
     * @return FileSourceService
     */
    protected function getS2B2CFileSourceService()
    {
        return $this->biz->service('S2B2C:FileSourceService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }
}
