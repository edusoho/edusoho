<?php

namespace Biz\CloudPlatform\Facade\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\CloudPlatform\Facade\ResourceFacade;

class ResourceFacadeImpl extends BaseFacade implements ResourceFacade
{
    public function getPlayerContext($file, $userAgent = '')
    {
        $context = [];

        //是否开启加密增强
        $storageSetting = $this->getSettingService()->get('storage');
        $context['isEncryptionPlus'] = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['enable_hls_encryption_plus'];

        $context['agentInWhiteList'] = $this->agentInWhiteList($userAgent);

        //对不同的资源类型，添加不同的配置参数
        $method = 'prepare'.ucfirst($file['type']).'Context';
        if (function_exists($method)) {
            $context = $this->$method($file, $context);
        }

        //获取用于权限验证的token和资源编码
        $context['token'] = $this->makePlayToken($file);
        $context['resNo'] = $file['globalId'];

        //转码状态
        $context['isFinishConvert'] = $file['storage'] != 'cloud' || !in_array($file['type'], ['ppt', 'document','video']) || $file['convertStatus'] == 'success';

        return $context;
    }

    protected function prepareVideoContext($file, $context)
    {
        $storageSetting = $this->getSettingService()->get('storage');
        //是否加入片头信息
        $isShowVideoHeader = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['video_header'];
        $videoHeaderLength = null;
        if ($isShowVideoHeader) {
            $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
            $videoHeaderLength = !empty($videoHeaderFile) && 'success' == $videoHeaderFile['convertStatus'] ? $videoHeaderFile['length'] : null;
        }
        $context['videoHeaderLength'] = $videoHeaderLength;

        //微网校用于是否支持 mobile 端判断
        $context['supportMobile'] = intval($this->getSettingService()->node('storage.support_mobile', 0));
        if ('cloud' == $file['storage']) {
            $context['jsPlayer'] = 'balloon-cloud-video-player';
        } else {
            $context['jsPlayer'] = 'local-video-player';
        }

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

    public function makePlayToken($file, $lifetime = 600, $payload = [])
    {
        // to do: S2B2C 也要更改相应的播放器
        if ('supplier' == $file['storage']) {
            return $this->getS2B2CFileSourceService()->player($file['globalId'], true);
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

        $paths = [
            'player' => 'js-sdk/sdk-v1.js',
            'newPlayer' => 'js-sdk/sdk-v2.js',
            'audio' => 'js-sdk-v2/sdk-v1.js',
            'video' => 'js-sdk-v2/sdk-v1.js',
            'uploader' => 'js-sdk/uploader/sdk-2.1.0.js',
            'old_uploader' => 'js-sdk/uploader/sdk-v1.js',
            'old_document' => 'js-sdk/document-player/v7/viewer.html',
            'faq' => 'js-sdk/faq/sdk-v1.js',
        ];

        if (isset($paths[$type])) {
            $path = $paths[$type];
        } else {
            $path = $type;
        }

        $timestamp = round(time() / 100);

        return '//'.trim($cdnHost, "\/").'/'.$path.'?'.$timestamp;
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
}
