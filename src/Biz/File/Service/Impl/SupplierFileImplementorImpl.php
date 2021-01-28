<?php

namespace Biz\File\Service\Impl;

use AppBundle\Common\CloudFileStatusToolkit;
use Biz\BaseService;
use Biz\File\Dao\UploadFileDao;
use Biz\File\Service\FileImplementor;
use Biz\S2B2C\Service\FileSourceService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SupplierFileImplementorImpl extends BaseService implements FileImplementor
{
    public function moveFile($targetType, $targetId, UploadedFile $originalFile = null, $data = [])
    {
    }

    public function getFile($file)
    {
        $file['convertParams'] = $this->decodeMetas($file['convertParams']);
        $file['metas'] = $this->decodeMetas($file['metas']);
        $file['metas2'] = $this->decodeMetas($file['metas2']);

        return $file;
    }

    public function getFullFile($file)
    {
        $fileInfo = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
        $resourceFile = $this->getS2B2CFacedService()->getS2B2CService()->getProductResource(
            "/resources/{$fileInfo['globalId']}",
            $fileInfo,
            ['canNoSdInMetas' => 1]
        );

        return $this->mergeResourceFile($fileInfo, $resourceFile);
    }

    public function getFileByGlobalId($globalId)
    {
        $file = $this->getUploadFileDao()->getByGlobalId($globalId);

        $fileInfo = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
        $resourceFile = $this->getS2B2CFacedService()->getS2B2CService()->getProductResource(
            "/resources/{$globalId}",
            $fileInfo,
            []
        );

        return $this->mergeResourceFile($file, $resourceFile);
    }

    public function player($globalId, $ssl = false)
    {
        $params = [];
        if ($ssl) {
            $params['protocol'] = 'https';
        }

        $file = $this->getUploadFileDao()->getByGlobalId($globalId);

        $fileInfo = $this->getS2B2CFileSourceService()->getFullFileInfo($file);

        return $this->getS2B2CFacedService()->getS2B2CService()->getProductResourcePlayer(
            "/resources/{$file['globalId']}/player",
            $fileInfo,
            $params
        );
    }

    public function addFile($targetType, $targetId, array $fileInfo = [], UploadedFile $originalFile = null)
    {
    }

    public function reconvert($globalId, $options)
    {
    }

    public function retryTranscode(array $globalIds)
    {
    }

    public function getResourcesStatus($options)
    {
    }

    public function getAudioServiceStatus()
    {
    }

    public function deleteFile($file)
    {
    }

    public function updateFile($globalId, $fields)
    {
    }

    public function prepareUpload($params)
    {
    }

    public function initFormUpload($file)
    {
    }

    public function initUpload($file)
    {
    }

    public function getUploadAuth($params)
    {
    }

    public function resumeUpload($file, $initParams)
    {
    }

    public function download($globalId)
    {
    }

    public function getDownloadFile($file, $ssl = false)
    {
        $params = [];
        if ($ssl) {
            $params['protocol'] = 'https';
        }

        $file = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
        $download = $this->getS2B2CFacedService()->getS2B2CService()->getProductResDownload("/resources/{$file['globalId']}/download", $file, $params);
        $download['type'] = 'url';

        return $download;
    }

    public function getDefaultHumbnails($globalId)
    {
    }

    public function getThumbnail($globalId, $options)
    {
    }

    public function getStatistics($options)
    {
    }

    public function findFiles($files, $conditions)
    {
    }

    public function finishedUpload($file, $params)
    {
    }

    public function search($conditions)
    {
    }

    private function mergeResourceFile($localFile, $resourceFile)
    {
        if (empty($localFile)) {
            $localFile = [
                'id' => 0,
                'storage' => 'supplier',
                'globalId' => $resourceFile['no'],
                'usedCount' => 0,
                'hashId' => $resourceFile['reskey'],
                'fileSize' => $resourceFile['size'],
                'filename' => $resourceFile['name'],
            ];
        }

        unset($resourceFile['id']);

        $file = array_merge($localFile, $resourceFile);
        $file = $this->parseConvertStatus($file);
        $file = $this->parseConvertParamsAndMetas($file);

        $file['storage'] = 'supplier';

        return $file;
    }

    protected function decodeMetas($metas)
    {
        if (empty($metas)) {
            return [];
        }

        if (is_array($metas)) {
            return $metas;
        }

        return json_decode($metas, true);
    }

    protected function parseConvertStatus($file)
    {
        if (!isset($file['processStatus'])) {
            return $file;
        }

        $processStatus = $file['processStatus'];

        $file['convertStatus'] = CloudFileStatusToolkit::convertProcessStatus($processStatus);

        if (!empty($file['levelsStatus'])) {
            $isAllLevelsOk = true;
            foreach ($file['levelsStatus'] as $levelStatus) {
                if ('ok' != $levelStatus['status']) {
                    $isAllLevelsOk = false;
                    break;
                }
            }
            if ($isAllLevelsOk) {
                unset($file['levelsStatus']);
            }
        }

        return $file;
    }

    protected function parseConvertParamsAndMetas($file)
    {
        $file['convertParams'] = [];
        $file['metas2'] = [];

        if (!empty($file['directives']['output'])) {
            if ('video' == $file['type']) {
                $file['convertParams'] = [
                    'convertor' => 'HLSEncryptedVideo',
                    'videoQuality' => isset($file['directives']['videoQuality']) ? $file['directives']['videoQuality'] : 'normal',
                    'audioQuality' => isset($file['directives']['audioQuality']) ? $file['directives']['audioQuality'] : 'normal',
                ];

                if (isset($file['metas']['levels'])) {
                    foreach ($file['metas']['levels'] as $key => $value) {
                        $value['type'] = $key;
                        $value['cmd']['hlsKey'] = $file['metas']['levels'][$key]['hlsKey'];
                        $file['metas']['levels'][$key] = $value;
                    }

                    $file['metas2'] = $file['metas']['levels'];
                }

                if (isset($file['metas']['audiolevels'])) {
                    foreach ($file['metas']['audiolevels'] as $key => $value) {
                        $value['type'] = $key;
                        $value['cmd']['hlsKey'] = $file['metas']['audiolevels'][$key]['hlsKey'];
                        $file['audioMetas']['levels'][$key] = $value;
                    }

                    $file['audioMetas2'] = $file['audioMetas']['levels'];
                }

                if (isset($file['directives']['watermarks'])) {
                    $file['convertParams']['hasVideoWatermark'] = 1;
                }

                if (isset($file['metas']['mp4levels']) && !empty($file['metas']['mp4levels'])) {
                    $file['hasMp4'] = 1;
                }
            } elseif (in_array($file['type'], ['ppt', 'document'])) {
                $file['convertParams'] = [
                    'convertor' => $file['directives']['output'],
                ];
                $file['metas2'] = $file['metas'];
            } elseif ('audio' == $file['type']) {
                $file['convertParams'] = [
                    'convertor' => $file['directives']['output'],
                    'videoQuality' => 'normal',
                    'audioQuality' => 'normal',
                ];
                $file['metas2'] = isset($file['metas']['levels']) ? $file['metas']['levels'] : [];
            }
        }

        return $file;
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return FileSourceService
     */
    protected function getS2B2CFileSourceService()
    {
        return $this->createService('S2B2C:FileSourceService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacedService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
