<?php
namespace Biz\Media\Service\Impl;

use Biz\BaseService;
use Biz\Media\Service\MediaService;
use Topxia\Service\Util\CloudClientFactory;

class MediaServiceImpl extends BaseService implements MediaService
{
    public function getVideoPlayUrl($globalId, $options)
    {
        $defaultOptions = array(
            'format'   => '',
            'fromApi'  => false,
            'times'    => 1,
            'duration' => 3600,
            'line'     => ''
        );

        $options = array_merge($defaultOptions, $options);
        $file    = $this->getUploadFileService()->getFileByGlobalId($globalId);
        if (empty($file)) {
            throw $this->createNotFoundException($this->trans('无效文件'));
        }

        if ($file['type'] != 'video') {
            throw $this->createServiceException($this->trans('非视频文件'));
        }

        $factory = new CloudClientFactory();
        $client  = $factory->createClient();
        if (!empty($file['metas2']) && !empty($file['metas2']['sd']['key'])) {
            if (isset($file['convertParams']['convertor']) && ($file['convertParams']['convertor'] == 'HLSEncryptedVideo')) {
                $token = $this->getTokenService()->makeToken('hls.playlist', array(
                    'data'     => array(
                        'id'      => $file['id'],
                        'fromApi' => $options['fromApi']
                    ),
                    'times'    => $options['times'],
                    'duration' => $options['duration']
                ));

                $url = $this->getHttpHost()."/hls/{$file['id']}/playlist/{$token['token']}.m3u8?hideBeginning=1&format={$options['format']}&line=".$options['line'];
            } else {
                $url = $client->generateHLSQualitiyListUrl($file['metas2'], $options['duration']);
            }
        } else {
            if (!empty($file['metas']) && !empty($file['metas']['hd']['key'])) {
                $key = $file['metas']['hd']['key'];
            } else {
                if ($file['type'] == 'video') {
                    $key = null;
                } else {
                    $key = $file['hashId'];
                }
            }

            if ($key) {
                $url = $client->generateFileUrl($key, $options['duration']);
            }
        }

        return $url;
    }

    private function getHttpHost()
    {
        return $this->getKernel()->getEnvVariable('schemeAndHost');
    }

    public function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }

    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    protected function getCloudFileService()
    {
        return $this->getBiz()->service('CloudFile:CloudFileService');
    }

    protected function getTokenService()
    {
        return $this->getBiz()->service('User:TokenService');
    }
}
