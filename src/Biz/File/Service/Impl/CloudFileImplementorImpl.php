<?php

namespace Biz\File\Service\Impl;

use AppBundle\Common\CloudFileStatusToolkit;
use Biz\BaseService;
use Biz\CloudPlatform\Client\AbstractCloudAPI;
use Biz\Common\CommonException;
use Biz\File\Dao\UploadFileDao;
use Biz\File\Service\FileImplementor;
use Biz\File\UploadFileException;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use Biz\CloudPlatform\CloudAPIFactory;

class CloudFileImplementorImpl extends BaseService implements FileImplementor
{
    private $cloudApis = array();

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
        $cloudFile = $this->createApi('leaf', 'v1')->get("/resources/{$file['globalId']}", array('canNoSdInMetas' => 1));

        return $this->mergeCloudFile($file, $cloudFile);
    }

    public function getFileByGlobalId($globalId)
    {
        $cloudFile = $this->createApi('root', 'v1')->get('/resources/'.$globalId);
        $localFile = $this->getUploadFileDao()->getByGlobalId($globalId);

        return $this->mergeCloudFile($localFile, $cloudFile);
    }

    /**
     * @todo 暂时未使用，使用需要重构
     *
     * @param $targetType
     * @param $targetId
     * @param array             $fileInfo
     * @param UploadedFile|null $originalFile
     *
     * @return array
     *
     * @throws CommonException
     * @throws UploadFileException
     * @throws \Exception
     */
    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null)
    {
        if (!ArrayToolkit::requireds($fileInfo, array('filename', 'key', 'size'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (empty($fileInfo['globalId'])) {
            $this->createNewException(UploadFileException::GLOBALID_REQUIRED());
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

        if (!empty($fileInfo['lazyConvert'])) {
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
        return $this->createApi('root')->post("/resources/{$globalId}/processes", $options);
    }

    public function retryTranscode(array $globalIds)
    {
        if (!empty($globalIds)) {
            $params = array('nos' => $globalIds);

            return $this->createApi('root')->post('/resources/transcode_retry', $params);
        }

        return false;
    }

    public function getResourcesStatus($options)
    {
        if (isset($options['cursor'])) {
            return $this->createApi('root')->get('/resources_statuses', $options);
        }

        return array();
    }

    public function getAudioServiceStatus()
    {
        return $this->createApi('root')->get('/me/profile');
    }

    public function deleteFile($file)
    {
        if (!empty($file['globalId'])) {
            return $this->createApi('root')->delete("/resources/{$file['globalId']}");
        }

        return false;
    }

    public function player($globalId, $ssl = false)
    {
        $params = array();
        if ($ssl) {
            $params['protocol'] = 'https';
        }
        $player = $this->createApi('root')->get("/resources/{$globalId}/player", $params);

        return $player;
    }

    public function updateFile($globalId, $fields)
    {
        if (!empty($globalId)) {
            $cloudFile = $this->createApi('root')->post('/resources/'.$globalId, $fields);
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

    public function initFormUpload($file)
    {
        $params = array(
            'extno' => $file['id'],
            'bucket' => $file['bucket'],
            'reskey' => $file['hashId'],
            'hash' => $file['hash'],
            'name' => $file['fileName'],
            'size' => $file['fileSize'],
            'uploadType' => $file['uploadType'],
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

        $apiResult = $this->createApi('root')->post('/resources/upload_form_init', $params);
        $apiResult['fileId'] = $file['id'];
        $apiResult['globalId'] = $apiResult['no'];
        unset($apiResult['no']);

        return $apiResult;
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

        $apiResult = $this->createApi('root')->post('/resources/upload_init', $params);

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
        $apiResult = $this->createApi('root')->post("/resources/{$params['globalId']}/upload/auth", $params);

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

        $apiResult = $this->createApi('root')->post("/resources/{$file['globalId']}/upload_resume", $params);

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
        $download = $this->createApi('leaf')->get("/resources/{$globalId}/download");

        return $download;
    }

    public function getDownloadFile($file, $ssl = false)
    {
        $params = array();
        if ($ssl) {
            $params['protocol'] = 'https';
        }

        $download = $this->createApi('leaf')->get("/resources/{$file['globalId']}/download", $params);

        $download['type'] = 'url';

        return $download;
    }

    public function getDefaultHumbnails($globalId)
    {
        if (empty($globalId)) {
            return array();
        }

        $result = $this->createApi('root')->get("/resources/{$globalId}/default_thumbnails");

        return $result;
    }

    public function getThumbnail($globalId, $options)
    {
        return $this->createApi('root')->get("/resources/{$globalId}/thumbnail", $options);
    }

    public function getStatistics($options)
    {
        return $this->createApi('root')->get('/resources/data/statistics', $options);
    }

    public function findFiles($files, $conditions)
    {
        if (empty($files)) {
            return array();
        }
        $user = $this->getCurrentUser();

        $globalIds = array_unique(ArrayToolkit::column($files, 'globalId'));
        $globalIdsChunks = array_chunk($globalIds, 200);
        $data = array();
        $count = 0;
        if (!empty($user['isSecure'])) {
            $conditions['protocol'] = 'https';
        }
        foreach ($globalIdsChunks as $globalIdsChunk) {
            $conditions['limit'] = count($globalIdsChunk);
            $conditions['nos'] = implode(',', $globalIdsChunk);
            $result = $this->createApi('root', 'v1')->get('/resources', $conditions);
            if (!empty($result['data'])) {
                $data = array_merge($data, $result['data']);
                $count += $result['count'];
            }
        }

        $result = array(
            'data' => $data,
            'count' => $count,
        );

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
            $this->createNewException(UploadFileException::GLOBALID_REQUIRED());
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
        $api = $this->createApi('root');
        $result = $api->post("/resources/{$file['globalId']}/upload_finish", $params);
        $file = $api->get("/resources/{$file['globalId']}", array('refresh' => true));
        $result['convertStatus'] = 'none';
        $result['length'] = $file['length'];

        return $result;
    }

    public function search($conditions)
    {
        $url = '/resources?'.http_build_query($conditions);
        $result = $this->createApi('root', 'v1')->get($url);
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
        return $this->createApi('root')->post('/system_jobs/delete_user_all_video_resource_mp4',
            array('callback' => $callback));
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
     * @param string $node
     * @param string $version api版本
     *
     * @return mixed
     */
    protected function createApi($node = 'root', $version = AbstractCloudAPI::DEFAULT_API_VERSION)
    {
        $apiType = $node.'-'.$version;
        if (!isset($this->cloudApis[$apiType])) {
            $this->cloudApis[$apiType] = CloudAPIFactory::create($node, $version);
        }

        return $this->cloudApis[$apiType];
    }

    /**
     * 仅限单元测试mockApi使用
     *
     * @param string $node
     * @param $mockApi
     */
    public function setApi($node = 'root', $mockApi, $version = AbstractCloudAPI::DEFAULT_API_VERSION)
    {
        $apiType = $node.'-'.$version;
        $this->cloudApis[$apiType] = $mockApi;
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
