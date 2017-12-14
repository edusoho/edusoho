<?php

namespace Biz\File\Service\Impl;

use Biz\BaseService;
use Biz\File\Dao\UploadFileDao;
use Biz\File\Service\FileImplementor;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use Biz\CloudPlatform\CloudAPIFactory;

class CloudFileImplementorImpl extends BaseService implements FileImplementor
{
    private $cloudClient;

    public function moveFile($targetType, $targetId, UploadedFile $originalFile = null, $data = array())
    {
    }

    public function getFile($file)
    {
        $file['convertParams'] = $this->decodeMetas($file['convertParams']);
        $file['metas'] = $this->decodeMetas($file['metas']);
        $file['metas2'] = $this->decodeMetas($file['metas2']);
        // $file['path'] = $this->getCloudClient()->getFileUrl($file['hashId'],$file['targetId'],$file['targetType']);
        return $file;
    }

    public function getFullFile($file)
    {
        $api = CloudAPIFactory::create('leaf');
        $cloudFile = $api->get("/resources/{$file['globalId']}");

        return $this->mergeCloudFile($file, $cloudFile);
    }

    public function getFileByGlobalId($globalId)
    {
        $api = CloudAPIFactory::create('root');
        $cloudFile = $api->get('/resources/'.$globalId);
        $localFile = $this->getUploadFileDao()->getByGlobalId($globalId);

        return $this->mergeCloudFile($localFile, $cloudFile);
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null)
    {
        if (!ArrayToolkit::requireds($fileInfo, array('filename', 'key', 'size'))) {
            throw $this->createServiceException('参数缺失，添加用户文件失败!');
        }

        if (empty($fileInfo['globalId'])) {
            throw $this->createInvalidArgumentException('添加云文件，缺少globalId');
        }

        $uploadFile = array();
        $uploadFile['globalId'] = $fileInfo['globalId'];
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType;
        $uploadFile['hashId'] = $fileInfo['key'];
        $uploadFile['filename'] = $fileInfo['filename'];
        $uploadFile['ext'] = pathinfo($uploadFile['filename'], PATHINFO_EXTENSION);
        $uploadFile['fileSize'] = (int) $fileInfo['size'];
        $uploadFile['etag'] = empty($fileInfo['etag']) ? '' : $fileInfo['etag'];
        $uploadFile['length'] = empty($fileInfo['length']) ? 0 : intval($fileInfo['length']);

        $uploadFile['metas'] = $this->encodeMetas(empty($fileInfo['metas']) ? array() : $fileInfo['metas']);
        $uploadFile['metas2'] = $this->encodeMetas(empty($fileInfo['metas2']) ? array() : $fileInfo['metas2']);

        if ($fileInfo['lazyConvert']) {
            $fileInfo['convertHash'] = "lazy-{$uploadFile['hashId']}";
        }

        if (empty($fileInfo['convertHash'])) {
            $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = '';
        } else {
            $uploadFile['convertHash'] = "{$fileInfo['convertHash']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = $fileInfo['convertParams'];
        }

        $uploadFile['type'] = FileToolkit::getFileTypeByExtension($uploadFile['ext']);
        $uploadFile['storage'] = 'cloud';
        $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'];
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();

        return $uploadFile;
    }

    public function reconvert($globalId, $options)
    {
        $api = CloudAPIFactory::create('root');

        return $api->post("/resources/{$globalId}/processes", $options);
    }

    public function retryTranscode(array $globalIds)
    {
        if (!empty($globalIds)) {
            $api = CloudAPIFactory::create('root');
            $params = array('nos' => $globalIds);

            return $api->post('/resources/transcode_retry', $params);
        }

        return false;
    }
    
    public function getAudioServiceStatus()
    {
        $api = CloudAPIFactory::create('root');

        return $api->get('/me/profile');
    }

    public function deleteFile($file)
    {
        if (!empty($file['globalId'])) {
            $api = CloudAPIFactory::create('root');

            return $api->delete("/resources/{$file['globalId']}");
        }

        return false;
    }

    public function player($globalId, $ssl = false)
    {
        $api = CloudAPIFactory::create('leaf');
        $params = array();
        if ($ssl) {
            $params['protocol'] = 'https';
        }
        $player = $api->get("/resources/{$globalId}/player", $params);

        return $player;
    }

    public function updateFile($globalId, $fields)
    {
        if (!empty($globalId)) {
            $api = CloudAPIFactory::create('root');
            $cloudFile = $api->post('/resources/'.$globalId, $fields);
            $localFile = $this->getUploadFileDao()->getByGlobalId($globalId);

            return $this->mergeCloudFile($localFile, $cloudFile);
        }

        return false;
    }

    public function prepareUpload($params)
    {
        $file = array();
        $file['filename'] = empty($params['fileName']) ? '' : $params['fileName'];

        $pos = strrpos($file['filename'], '.');
        $file['ext'] = empty($pos) ? '' : substr($file['filename'], $pos + 1);

        $file['fileSize'] = empty($params['fileSize']) ? 0 : $params['fileSize'];
        $file['status'] = 'uploading';
        $file['targetId'] = $params['targetId'];
        $file['targetType'] = $params['targetType'];
        $file['storage'] = 'cloud';

        $file['type'] = FileToolkit::getFileTypeByExtension($file['ext']);

        $file['updatedUserId'] = empty($params['userId']) ? 0 : $params['userId'];
        $file['updatedTime'] = time();
        $file['createdUserId'] = $file['updatedUserId'];
        $file['createdTime'] = $file['updatedTime'];

        // 以下参数在cloud模式下弃用，填充随机值
        $keySuffix = date('Ymdhis').'-'.substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 16);
        $key = "{$params['targetType']}-{$params['targetId']}/{$keySuffix}";
        $file['hashId'] = $key;
        $file['etag'] = $file['hashId'];
        $file['convertHash'] = $file['hashId'];

        return $file;
    }

    public function initUpload($file)
    {
        $params = array(
            'extno' => $file['id'],
            'bucket' => $file['bucket'],
            'reskey' => $file['hashId'],
            'hash' => $file['hash'],
            'name' => $file['fileName'],
            'size' => $file['fileSize'],
        );
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

        $api = CloudAPIFactory::create();
        $apiResult = $api->post('/resources/upload_init', $params);

        $result = array();

        $result['globalId'] = $apiResult['no'];
        $result['hashId'] = $file['hashId'];
        $result['outerId'] = $file['id'];
        $result['uploadMode'] = $apiResult['uploadMode'];
        $result['uploadUrl'] = $apiResult['uploadUrl'];
        $result['uploadProxyUrl'] = '';
        $result['uploadToken'] = $apiResult['uploadToken'];

        return $result;
    }

    public function getUploadAuth($params)
    {
        $api = CloudAPIFactory::create('root');

        $apiResult = $api->post("/resources/{$params['globalId']}/upload/auth", $params);

        return $apiResult;
    }

    public function resumeUpload($file, $initParams)
    {
        $params = array(
            'bucket' => $initParams['bucket'],
            'extno' => $file['id'],
            'size' => $initParams['fileSize'],
            'name' => $initParams['fileName'],
            'hash' => $initParams['hash'],
        );

        $api = CloudAPIFactory::create('root');
        $apiResult = $api->post("/resources/{$file['globalId']}/upload_resume", $params);

        if (empty($apiResult['resumed']) || ('ok' !== $apiResult['resumed'])) {
            return null;
        }

        $result = array();

        $result['globalId'] = $file['globalId'];
        $result['outerId'] = $file['id'];
        $result['hashId'] = $file['hashId'];
        $result['resumed'] = $apiResult['resumed'];

        $result['uploadMode'] = $apiResult['uploadMode'];
        $result['uploadUrl'] = $apiResult['uploadUrl'];
        $result['uploadProxyUrl'] = '';
        $result['uploadToken'] = $apiResult['uploadToken'];

        return $result;
    }

    public function download($globalId)
    {
        $api = CloudAPIFactory::create('leaf');
        $download = $api->get("/resources/{$globalId}/download");

        return $download;
    }

    public function getDownloadFile($file, $ssl = false)
    {
        $params = array();
        if ($ssl) {
            $params['protocol'] = 'https';
        }

        $api = CloudAPIFactory::create('leaf');
        $download = $api->get("/resources/{$file['globalId']}/download", $params);
        $download['type'] = 'url';

        return $download;
    }

    public function getDefaultHumbnails($globalId)
    {
        if (empty($globalId)) {
            return array();
        }

        $api = CloudAPIFactory::create('root');
        $result = $api->get("/resources/{$globalId}/default_thumbnails");

        return $result;
    }

    public function getThumbnail($globalId, $options)
    {
        $api = CloudAPIFactory::create('root');

        return $api->get("/resources/{$globalId}/thumbnail", $options);
    }

    public function getStatistics($options)
    {
        $api = CloudAPIFactory::create('root');

        return $api->get('/resources/data/statistics', $options);
    }

    public function findFiles($files, $conditions)
    {
        if (empty($files)) {
            return array();
        }

        $conditions['nos'] = ArrayToolkit::column($files, 'globalId');
        $conditions['limit'] = count($conditions['nos']);
        $conditions['nos'] = implode(',', array_unique($conditions['nos']));

        $api = CloudAPIFactory::create('root');
        $result = $api->get('/resources', $conditions);

        if (empty($result['data'])) {
            return $files;
        }

        $cloudFiles = $result['data'];
        $cloudFiles = ArrayToolkit::index($cloudFiles, 'no');

        foreach ($files as $i => $file) {
            if (empty($cloudFiles[$file['globalId']])) {
                continue;
            }

            $files[$i] = $this->mergeCloudFile($file, $cloudFiles[$file['globalId']]);
        }

        return $files;
    }

    public function finishedUpload($file, $params)
    {
        if (empty($file['globalId'])) {
            throw $this->createInvalidArgumentException(sprintf('文件不存在%s，完成上传失败！', $params['globalId']));
        }

        $params = array(
            'length' => $params['length'],
            'name' => empty($params['filename']) ? $file['filename'] : $params['filename'],
            'size' => $params['size'],
            'extno' => $file['id'],
        );
        if ('attachment' == $file['targetType']) {
            $params['type'] = $file['targetType'];
        }
        $api = CloudAPIFactory::create('root');
        $result = $api->post("/resources/{$file['globalId']}/upload_finish", $params);
        $file = $api->get("/resources/{$file['globalId']}", array('refresh' => true));
        $result['convertStatus'] = 'none';
        $result['length'] = $file['length'];

        return $result;
    }

    public function search($conditions)
    {
        $api = CloudAPIFactory::create('root');
        $url = '/resources?'.http_build_query($conditions);
        $result = $api->get($url);
        $cloudFiles = $result['data'];

        $cloudFiles = ArrayToolkit::index($cloudFiles, 'no');
        $localFileIds = ArrayToolkit::column($cloudFiles, 'extno');

        $localFiles = $this->getUploadFileDao()->findByIds($localFileIds);
        $localFiles = ArrayToolkit::index($localFiles, 'globalId');
        $mergedFiles = array();

        foreach ($cloudFiles as $i => $cloudFile) {
            $localFile = empty($localFiles[$cloudFile['no']]) ? null : $localFiles[$cloudFile['no']];
            $mergedFiles[$i] = $this->mergeCloudFile($localFile, $cloudFile);
        }

        $result['data'] = $mergedFiles;

        return $result;
    }

    public function deleteMP4Files($callback)
    {
        $api = CloudAPIFactory::create('root');

        return $api->post('/system_jobs/delete_user_all_video_resource_mp4', array('callback' => $callback));
    }

    private function mergeCloudFile($localFile, $cloudFile)
    {
        if (empty($localFile)) {
            $localFile = array(
                'id' => 0,
                'storage' => 'cloud',
                'globalId' => $cloudFile['no'],
                'usedCount' => 0,
                'hashId' => $cloudFile['reskey'],
                'fileSize' => $cloudFile['size'],
                'filename' => $cloudFile['name'],
            );
        }

        unset($cloudFile['id']);

        $file = array_merge($localFile, $cloudFile);
        $file = $this->proccessConvertStatus($file);
        $file = $this->proccessConvertParamsAndMetas($file);

        $file['storage'] = 'cloud';

        return $file;
    }

    protected function getVideoWatermarkImages()
    {
        $setting = $this->getSettingService()->get('storage', array());

        if (empty($setting['video_embed_watermark_image']) || (2 != $setting['video_watermark'])) {
            return array();
        }

        $videoWatermarkImage = $this->biz['env']['base_url'].$this->biz['topxia.upload.public_url_path'].'/'.$setting['video_embed_watermark_image'];
        $pathinfo = pathinfo($videoWatermarkImage);

        $images = array();
        $heighs = array('240', '360', '480', '720', '1080');

        foreach ($heighs as $height) {
            $images[$height] = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$height}.{$pathinfo['extension']}";
        }

        return $images;
    }

    protected function encodeMetas($metas)
    {
        if (empty($metas)) {
            $metas = array();
        }

        if (is_array($metas)) {
            return $metas;
        }

        return json_encode($metas);
    }

    protected function decodeMetas($metas)
    {
        if (empty($metas)) {
            return array();
        }

        if (is_array($metas)) {
            return $metas;
        }

        return json_decode($metas, true);
    }

    protected function proccessConvertStatus($file)
    {
        if (!isset($file['processStatus'])) {
            return $file;
        }

        $statusMap = array(
            'none' => 'none',
            'waiting' => 'waiting',
            'processing' => 'doing',
            'ok' => 'success',
            'error' => 'error',
        );

        $file['convertStatus'] = $statusMap[$file['processStatus']];

        return $file;
    }

    protected function proccessConvertParamsAndMetas($file)
    {
        $file['convertParams'] = array();
        $file['metas2'] = array();

        if (!empty($file['directives']['output'])) {
            if ('video' == $file['type']) {
                $file['convertParams'] = array(
                    'convertor' => 'HLSEncryptedVideo',
                    'videoQuality' => isset($file['directives']['videoQuality']) ? $file['directives']['videoQuality'] : 'normal',
                    'audioQuality' => isset($file['directives']['audioQuality']) ? $file['directives']['audioQuality'] : 'normal',
                );

                if (isset($file['metas']['levels'])) {
                    foreach ($file['metas']['levels'] as $key => $value) {
                        $value['type'] = $key;
                        $value['cmd']['hlsKey'] = $file['metas']['levels'][$key]['hlsKey'];
                        $file['metas']['levels'][$key] = $value;
                    }

                    $file['metas2'] = $file['metas']['levels'];
                }

                if (isset($file['directives']['watermarks'])) {
                    $file['convertParams']['hasVideoWatermark'] = 1;
                }
            } elseif (in_array($file['type'], array('ppt', 'document'))) {
                $file['convertParams'] = array(
                    'convertor' => $file['directives']['output'],
                );
                $file['metas2'] = $file['metas'];
            } elseif ('audio' == $file['type']) {
                $file['convertParams'] = array(
                    'convertor' => $file['directives']['output'],
                    'videoQuality' => 'normal',
                    'audioQuality' => 'normal',
                );
                $file['metas2'] = $file['metas']['levels'];
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
}
