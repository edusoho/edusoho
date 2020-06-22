<?php
namespace Biz\CloudPlatform\Facade\Impl;

use Biz\CloudPlatform\Facade\ResourceFacade;
use AppBundle\Common\ArrayToolkit;

class ResourceFacadeImpl extends BaseFacade implements ResourceFacade
{
    public function getPlayerContext($file, $userAgent = '')
    {
        $storageSetting = $this->getSettingService()->get('storage');
        //是否开启加密增强
        $isEncryptionPlus = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['enable_hls_encryption_plus'];
        //是否加入片头信息
        $isShowVideoHeader = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['video_header'];
        $videoHeaderLength = null;
        if ($isShowVideoHeader) {
            $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
            $videoHeaderLength = !empty($videoHeaderFile) && 'success' == $videoHeaderFile['convertStatus'] ? $videoHeaderFile['length'] : null;
        }

        $playToken = $this->makePlayToken($file);

        return [
            'isEncryptionPlus' => $isEncryptionPlus,
            'videoHeaderLength' => $videoHeaderLength,
            'agentInWhiteList' => $this->agentInWhiteList($userAgent),
            'token' => $playToken,
            'resNo' => $file['globalId'],
        ];
    }

    public function makePlayToken($file, $lifetime = 600, $payload = array())
    {
        // if ('supplier' == $file['storage']) {
        //     return $this->getS2B2CFileSourceService()->player($file['globalId'], true);
        // }

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
            'video' => 'js-sdk-v2/sdk-v1.js',
            'uploader' => 'js-sdk/uploader/sdk-2.1.0.js',
            'old_uploader' => 'js-sdk/uploader/sdk-v1.js',
            'old_document' => 'js-sdk/document-player/v7/viewer.html',
            'faq' => 'js-sdk/faq/sdk-v1.js',
            'audio' => 'js-sdk/audio-player/sdk-v1.js',
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