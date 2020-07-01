<?php

namespace Biz\Player\Service\Impl;

use Biz\BaseService;
use Biz\File\Service\FileImplementor;
use Biz\File\Service\UploadFileService;
use Biz\MaterialLib\Service\MaterialLibService;
use Biz\Player\PlayerException;
use Biz\Player\Service\PlayerService;
use Biz\S2B2C\Service\FileSourceService;
use Biz\System\Service\SettingService;
use Biz\User\Service\TokenService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PlayerServiceImpl extends BaseService implements PlayerService
{
    public function getAudioAndVideoPlayerType($file)
    {
        switch ($file['type']) {
            case 'audio':
                return 'audio-player';
            case 'video':
                return 'local' == $file['storage'] ? 'local-video-player' : 'balloon-cloud-video-player';
            default:
                return null;
        }
    }

    public function getVideoFilePlayer($file, $agentInWhiteList, $context, $ssl)
    {
        $storageSetting = $this->getSettingService()->get('storage');

        $isEncryptionPlus = isset($storageSetting['enable_hls_encryption_plus']) && (bool) $storageSetting['enable_hls_encryption_plus'];

        if (!$this->isHiddenVideoHeader()) {
            // 加入片头信息
            $videoHeaderFile = $this->getUploadFileService()->getFileByTargetType('headLeader');
            if (!empty($videoHeaderFile) && 'success' == $videoHeaderFile['convertStatus']) {
                $context['videoHeaderLength'] = $videoHeaderFile['length'];
            }
        }

        if (!empty($file['convertParams']['hasVideoWatermark'])) {
            $file['videoWatermarkEmbedded'] = 1;
        }

        $result = $this->getPlayerByFile($file, $ssl);

        if (isset($result['subtitles'])) {
            $this->filterSubtitles($result['subtitles']);
            $context['subtitles'] = $result['subtitles'];
        }

        // 临时修复手机浏览器端视频不能播放的问题
        if ($agentInWhiteList) {
            //手机浏览器不弹题
            $context['hideQuestion'] = 1;
            $supportMobile = isset($storageSetting['support_mobile']) ? $storageSetting['support_mobile'] : 0;
            if (1 == $supportMobile && isset($file['mcStatus']) && 'yes' == $file['mcStatus']) {
                $mp4Url = isset($result['mp4url']) ? $result['mp4url'] : '';
                $isEncryptionPlus = false;
            }
        }

        return [
            'resId' => $file['globalId'],
            'mp4Url' => isset($mp4Url) ? $mp4Url : null,
            'isEncryptionPlus' => $isEncryptionPlus,
            'context' => $context,
        ];
    }

    public function getVideoPlayUrl($file, $context, $ssl)
    {
        if (in_array($file['storage'], ['cloud', 'supplier'])) {
            if (!empty($file['metas2'])) {
                if (isset($file['convertParams']['convertor']) && ('HLSEncryptedVideo' == $file['convertParams']['convertor'])) {
                    $hideBeginning = isset($context['hideBeginning']) ? $context['hideBeginning'] : false;
                    $context['hideBeginning'] = $this->isHiddenVideoHeader($hideBeginning);
                    $token = $this->makeToken('hls.playlist', $file['id'], $context);
                    $params = [
                        'id' => $file['id'],
                        'token' => $token['token'],
                    ];

                    return [
                        'route' => 'hls_playlist',
                        'params' => $params,
                        'referenceType' => UrlGeneratorInterface::ABSOLUTE_URL,
                    ];
                } else {
                    $this->createNewException(PlayerException::NOT_SUPPORT_TYPE());
                }
            } else {
                $result = [];
                if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                    $key = $file['metas']['hd']['key'];
                } else {
                    $key = $file['hashId'];
                }

                if ($key) {
                    $result = $this->getPlayerByFile($file, $ssl);
                }
            }

            return [
                'url' => isset($result['url']) ? $result['url'] : '',
            ];
        } else {
            $token = $this->makeToken('local.media', $file['id']);
            $params = [
                'id' => $file['id'],
                'token' => $token['token'],
                'ext' => $file['ext'],
            ];

            return [
                'route' => 'player_local_media',
                'params' => $params,
                'referenceType' => UrlGeneratorInterface::ABSOLUTE_URL,
            ];
        }
    }

    public function isHiddenVideoHeader($isHidden = false)
    {
        $storage = $this->getSettingService()->get('storage');
        if (!empty($storage) && array_key_exists('video_header', $storage) && $storage['video_header'] && !$isHidden) {
            return false;
        }

        return true;
    }

    /**
     * @param $doc
     * @param $ssl
     *
     * @return array
     *
     * @throws \Biz\Player\PlayerException
     * @throws \Exception
     */
    public function getDocFilePlayer($doc, $ssl)
    {
        $file = $this->getUploadFileService()->getFullFile($doc['mediaId']);

        if (empty($file) || empty($file['globalId'])) {
            $error = ['code' => 'error', 'message' => '抱歉，文档文件不存在，暂时无法学习。'];

            return [[], $error];
        }

        if ('document' != $file['type']) {
            $this->createNewException(PlayerException::FILE_TYPE_INVALID());
        }

        $result = $this->getPlayerByFile($file, $ssl);
        $result['resId'] = $file['globalId'];

        $isConvertNotSuccess = isset($file['convertStatus']) && FileImplementor::CONVERT_STATUS_SUCCESS != $file['convertStatus'];

        if ($isConvertNotSuccess) {
            if (FileImplementor::CONVERT_STATUS_ERROR == $file['convertStatus']) {
                $message = '文档转换失败，请到课程文件管理中，重新转换。';
                $error = ['code' => 'error', 'message' => $message];
            } else {
                $error = ['code' => 'processing', 'message' => '文档还在转换中，还不能查看，请稍等。'];
            }
        } else {
            $error = [];
        }

        return [$result, $error];
    }

    public function getPptFilePlayer($ppt, $ssl)
    {
        $file = $this->getUploadFileService()->getFullFile($ppt['mediaId']);

        $error = [];
        if (empty($file) || 'ppt' !== $file['type']) {
            $error = ['code' => 'error', 'message' => '抱歉，PPT文件不存在，暂时无法学习。'];
        }

        if (isset($file['convertStatus']) && 'success' != $file['convertStatus']) {
            if ('error' == $file['convertStatus']) {
                $message = 'PPT文档转换失败，请到课程文件管理中，重新转换。';
                $error['code'] = 'error';
                $error['message'] = $message;
            } else {
                $error['code'] = 'processing';
                $error['message'] = 'PPT文档还在转换中，还不能查看，请稍等。';
            }
        }
        $result = $this->getPlayerByFile($file, $ssl);
        $result['resId'] = $file['globalId'];

        if (isset($result['error'])) {
            $error['code'] = 'error';
            $error['message'] = $result['error'];
        }

        return [$result, $error];
    }

    public function getFlashFilePlayer($flash, $ssl)
    {
        return $this->getPlayerByFile($flash, $ssl);
    }

    private function filterSubtitles(&$subtitles)
    {
        foreach ($subtitles as &$subtitle) {
            $subtitle['name'] = rtrim($subtitle['name'], '.srt');
        }
    }

    protected function makeToken($type, $fileId, $context = [])
    {
        $fields = [
            'data' => [
                'id' => $fileId,
            ],
            'times' => 10,
            'duration' => 3600,
            'userId' => $this->getCurrentUser()->getId(),
        ];

        if (isset($context['watchTimeLimit'])) {
            $fields['data']['watchTimeLimit'] = $context['watchTimeLimit'];
        }

        if (isset($context['hideBeginning'])) {
            $fields['data']['hideBeginning'] = $context['hideBeginning'];
        }

        $token = $this->getTokenService()->makeToken($type, $fields);

        return $token;
    }

    protected function getPlayerByFile($file, $ssl = false)
    {
        if ('cloud' == $file['storage']) {
            return $this->getMaterialLibService()->player($file['globalId'], $ssl);
        }

        if ('supplier' == $file['storage']) {
            return $this->getS2B2CFileSourceService()->player($file['globalId'], $ssl);
        }

        return [];
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return FileSourceService
     */
    protected function getS2B2CFileSourceService()
    {
        return $this->createService('S2B2C:FileSourceService');
    }
}
