<?php
namespace Topxia\Service\File\Impl;

use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\File\Convertor\ConvertorFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudFileImplementorImpl extends BaseService implements FileImplementor
{
    private $cloudClient;

    public function getFile($file)
    {
        $file['convertParams'] = $this->decodeMetas($file['convertParams']);
        $file['metas']         = $this->decodeMetas($file['metas']);
        $file['metas2']        = $this->decodeMetas($file['metas2']);
        // $file['path'] = $this->getCloudClient()->getFileUrl($file['hashId'],$file['targetId'],$file['targetType']);
        return $file;
    }

    public function getFullFile($file)
    {
        $api       = CloudAPIFactory::create('leaf');
        $cloudFile = $api->get("/resources/{$file['globalId']}");

        return $this->mergeCloudFile($file, $cloudFile);
    }

    public function getFileByGlobalId($globalId)
    {
        $api       = CloudAPIFactory::create('root');
        $cloudFile = $api->get("/resources/".$globalId);
        $localFile = $this->getUploadFileDao()->getFileByGlobalId($globalId);
        return $this->mergeCloudFile($localFile, $cloudFile);
    }

    public function addFile($targetType, $targetId, array $fileInfo = array(), UploadedFile $originalFile = null)
    {
        if (!ArrayToolkit::requireds($fileInfo, array('filename', 'key', 'size'))) {
            throw $this->createServiceException('参数缺失，添加用户文件失败!');
        }

        $uploadFile               = array();
        $uploadFile['targetId']   = $targetId;
        $uploadFile['targetType'] = $targetType;
        $uploadFile['hashId']     = $fileInfo['key'];
        $uploadFile['filename']   = $fileInfo['filename'];
        $uploadFile['ext']        = pathinfo($uploadFile['filename'], PATHINFO_EXTENSION);
        $uploadFile['size']       = (int) $fileInfo['size'];
        $uploadFile['etag']       = empty($fileInfo['etag']) ? '' : $fileInfo['etag'];
        $uploadFile['length']     = empty($fileInfo['length']) ? 0 : intval($fileInfo['length']);

        $uploadFile['metas']  = $this->encodeMetas(empty($fileInfo['metas']) ? array() : $fileInfo['metas']);
        $uploadFile['metas2'] = $this->encodeMetas(empty($fileInfo['metas2']) ? array() : $fileInfo['metas2']);

        if ($fileInfo['lazyConvert']) {
            $fileInfo['convertHash'] = "lazy-{$uploadFile['hashId']}";
        }

        if (empty($fileInfo['convertHash'])) {
            $uploadFile['convertHash']   = "ch-{$uploadFile['hashId']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = '';
        } elseif ('document' == FileToolkit::getFileTypeByExtension($uploadFile['ext'])) {
            $uploadFile['convertHash']   = "{$fileInfo['convertHash']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = $fileInfo['convertParams'];
        } else {
            $uploadFile['convertHash']   = "{$fileInfo['convertHash']}";
            $uploadFile['convertStatus'] = 'none';
            $uploadFile['convertParams'] = $fileInfo['convertParams'];
        }

        $uploadFile['type']          = FileToolkit::getFileTypeByExtension($uploadFile['ext']);
        $uploadFile['canDownload']   = empty($uploadFile['canDownload']) ? 0 : 1;
        $uploadFile['storage']       = 'cloud';
        $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'];
        $uploadFile['updatedTime']   = $uploadFile['createdTime']   = time();

        return $uploadFile;
    }

    public function saveConvertResult($file, array $result = array())
    {
        if (empty($result['id'])) {
            throw new \RuntimeException('数据中id不能为空');
        }

        if ($result['code'] != 0) {
            $file['convertStatus'] = 'error';
            return $file;
        }

        if (empty($file['convertParams']['convertor'])) {
            $file['convertStatus'] = 'error';
            return $file;
        }

        $convertor = $this->getConvertor($file['convertParams']['convertor']);

        $file = $convertor->saveConvertResult($file, $result);

        return $file;
    }

    public function reconvert($globalId, $options)
    {
        $api = CloudAPIFactory::create('root');
        return $api->post("/resources/{$globalId}/processes", $options);
    }

    public function reconvertFile($file, $convertCallback, $pipeline = null)
    {
        if (empty($file['convertParams'])) {
            return;
        }

        $params = array(
            'convertCallback' => $convertCallback,
            'convertor'       => $file['convertParams']['convertor'],
            'convertParams'   => $file['convertParams']
        );

        if ($file['type'] == 'video') {
            $watermarks = $this->getVideoWatermarkImages();

            $file['convertParams']['hasVideoWatermark'] = empty($watermarks) ? 0 : 1;
            $file['convertParams']                      = $this->encodeMetas($file['convertParams']);

            $this->getUploadFileDao()->updateFile($file['id'], array('convertParams' => $file['convertParams']));
        }

        if ($pipeline) {
            $params['pipeline'] = $pipeline;
        }

        if (($file['type'] == 'video') && $watermarks) {
            $params['convertParams']['videoWatermarkImages'] = $watermarks;
        }

        $result = $this->getCloudClient()->reconvertFile($file['hashId'], $params);

        if (empty($result['persistentId'])) {
            return;
        }

        return $result['persistentId'];
    }

    public function reconvertOldFile($file, $convertCallback, $pipeline = null)
    {
        if (empty($file['convertParams'])) {
            return;
        }

        $params = array(
            'convertCallback' => $convertCallback,
            'convertor'       => $file['convertParams']['convertor'],
            'convertParams'   => $file['convertParams']
        );

        if ($file['type'] == 'video') {
            $watermarks = $this->getVideoWatermarkImages();

            $file['convertParams']['hasVideoWatermark'] = empty($watermarks) ? 0 : 1;
            $file['convertParams']                      = $this->encodeMetas($file['convertParams']);

            $this->getUploadFileDao()->updateFile($file['id'], array('convertParams' => $file['convertParams']));
        }

        if ($pipeline) {
            $params['pipeline'] = $pipeline;
        }

        if (($file['type'] == 'video') && $watermarks) {
            $params['convertParams']['videoWatermarkImages'] = $watermarks;
        }

        $task               = array();
        $task['key']        = $file['hashId'];
        $task['processor']  = 'video';
        $task['directives'] = array(
            'videoQuality' => $params['convertParams']['videoQuality'],
            'audioQuality' => $params['convertParams']['audioQuality'],
            'hlsKey'       => $params['convertParams']['hlsKey'],
            'hlsKeyUrl'    => $params['convertParams']['hlsKeyUrl']
        );

        if (!empty($params['convertParams']['videoWatermarkImages'])) {
            $task['directives']['watermarks'] = $params['convertParams']['videoWatermarkImages'];
        }

        $task['callbackUrl'] = $convertCallback;

        $api    = CloudAPIFactory::create('root');
        $result = $api->post('/processes', $task);

        if (empty($result['taskNo'])) {
            return;
        }

        return $result['taskNo'];
    }

    public function convertFile($file, $status, $result = null, $callback = null)
    {
        if ($status == 'doing') {
            $file['metas2']        = array();
            $file['convertStatus'] = 'doing';

            if ($file['type'] == 'ppt') {
                $cmds = $this->getCloudClient()->getPPTConvertCommands();

                foreach ($result as $item) {
                    $type = empty($cmds[$item['cmd']]) ? null : $cmds[$item['cmd']];

                    if (empty($type)) {
                        continue;
                    }

                    $file['metas2'][$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);

                    if ($callback) {
                        $result = $this->getCloudClient()->convertPPT($item['key'], $callback);
                    } else {
                        $result = $this->getCloudClient()->convertPPT($item['key']);
                    }

                    $file['metas2']['length']      = empty($result['length']) ? 0 : $result['length'];
                    $file['metas2']['imagePrefix'] = empty($result['imagePrefix']) ? '' : $result['imagePrefix'];
                }

                if (empty($file['metas2']['length'])) {
                    $file['convertStatus'] = 'error';
                }
            }
        } elseif ($status == 'success') {
            if ($file['type'] == 'video') {
                $cmds = $this->getCloudClient()->getVideoConvertCommands();
            } elseif ($file['type'] == 'audio') {
                $cmds = $this->getCloudClient()->getAudioConvertCommands();
            } else {
                $cmds = null;
            }

            if ($cmds) {
                $file['metas2'] = array();

                foreach ($result as $item) {
                    $type = empty($cmds[$item['cmd']]) ? null : $cmds[$item['cmd']];

                    if (empty($type)) {
                        continue;
                    }

                    if ($item['code'] != 0) {
                        continue;
                    }

                    if (empty($item['key'])) {
                        continue;
                    }

                    $file['metas2'][$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);
                }

                if (empty($file['metas2'])) {
                    $file['convertStatus'] = 'error';
                } else {
                    $file['convertStatus'] = 'success';
                }
            } else {
                $file['convertStatus'] = 'success';
            }
        } else {
            $file['convertStatus'] = $status;
        }

        $file['metas2'] = $this->encodeMetas(empty($file['metas2']) ? array() : $file['metas2']);

        return $file;
    }

    public function deleteFile($file)
    {
        if (!empty($file['globalId'])) {
            $api = CloudAPIFactory::create('root');
            return $api->delete("/resources/{$file['globalId']}");
        }

        return false;
    }

    public function makeUploadParams($rawParams)
    {
        if (!empty($rawParams['convertor'])) {
            $convertor = $this->getConvertor($rawParams['convertor']);

            $rawUploadParams = array(
                'convertor'       => $rawParams['convertor'],
                'convertCallback' => $rawParams['convertCallback'],
                'convertParams'   => $convertor->getCovertParams($rawParams),
                'duration'        => empty($rawParams['duration']) ? 18000 : $rawParams['duration'],
                'user'            => empty($rawParams['user']) ? 0 : $rawParams['user']
            );
        } else {
            $rawUploadParams = array(
                'convertor'       => null,
                'convertCallback' => null,
                'convertParams'   => array(),
                'duration'        => empty($rawParams['duration']) ? 18000 : $rawParams['duration'],
                'user'            => empty($rawParams['user']) ? 0 : $rawParams['user']
            );
        }

        $tokenAndUrl = $this->getCloudClient()->makeUploadParams($rawUploadParams);

        $key = null;

        if (!empty($rawParams['key'])) {
            $key = $rawParams['key'];
        }

        if (!empty($rawParams['targetType']) && isset($rawParams['targetId'])) {
            $keySuffix = date('Ymdhis').'-'.substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 16);
            $key       = "{$rawParams['targetType']}-{$rawParams['targetId']}/{$keySuffix}";
        }

        if (empty($key)) {
            throw $this->createServiceException("key error.");
        }

        $params                        = array();
        $params['storage']             = 'cloud';
        $params['url']                 = $tokenAndUrl['url'];
        $params['postParams']          = array();
        $params['postParams']['token'] = $tokenAndUrl['token'];
        $params['postParams']['key']   = $key;
        // $params['postParams']['x:convertKey'] = md5($params['postParams']['key']);
        $params['postParams']['x:convertParams'] = json_encode($rawUploadParams['convertParams']);

        return $params;
    }

    public function getMediaInfo($key, $mediaType)
    {
        return $this->getCloudClient()->getMediaInfo($key, $mediaType);
    }

    public function player($globalId)
    {
        $api    = CloudAPIFactory::create('leaf');
        $player = $api->get("/resources/{$globalId}/player");
        return $player;
    }

    public function updateFile($globalId, $fields)
    {
        if (!empty($globalId)) {
            $api       = CloudAPIFactory::create('root');
            $cloudFile = $api->post("/resources/".$globalId, $fields);
            $localFile = $this->getUploadFileDao()->getFileByGlobalId($globalId);
            return $this->mergeCloudFile($localFile, $cloudFile);
        }

        return false;
    }

    public function prepareUpload($params)
    {
        $file             = array();
        $file['filename'] = empty($params['fileName']) ? '' : $params['fileName'];

        $pos         = strrpos($file['filename'], '.');
        $file['ext'] = empty($pos) ? '' : substr($file['filename'], $pos + 1);

        $file['fileSize']   = empty($params['fileSize']) ? 0 : $params['fileSize'];
        $file['status']     = 'uploading';
        $file['targetId']   = $params['targetId'];
        $file['targetType'] = $params['targetType'];
        $file['storage']    = 'cloud';

        $file['type'] = FileToolkit::getFileTypeByExtension($file['ext']);

        $file['updatedUserId'] = empty($params['userId']) ? 0 : $params['userId'];
        $file['updatedTime']   = time();
        $file['createdUserId'] = $file['updatedUserId'];
        $file['createdTime']   = $file['updatedTime'];

        // 以下参数在cloud模式下弃用，填充随机值
        $keySuffix           = date('Ymdhis').'-'.substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 16);
        $key                 = "{$params['targetType']}-{$params['targetId']}/{$keySuffix}";
        $file['hashId']      = $key;
        $file['etag']        = $file['hashId'];
        $file['convertHash'] = $file['hashId'];

        return $file;
    }

    public function initUpload($file)
    {
        $params = array(
            "extno"  => $file['id'],
            "bucket" => $file['bucket'],
            "reskey" => $file['hashId'],
            "hash"   => $file['hash'],
            'name'   => $file['fileName'],
            'size'   => $file['fileSize']
        );
        if ($file['targetType'] == 'attachment') {
            $params['type'] = $file['targetType'];
        }
        if (isset($file['directives'])) {
            $params['directives'] = $file['directives'];
        }

        if ($file['type'] == 'video') {
            $watermarks = $this->getVideoWatermarkImages();

            if (!empty($watermarks)) {
                $params['directives']['watermarks'] = $watermarks;
            }
        }

        $api       = CloudAPIFactory::create();
        $apiResult = $api->post('/resources/upload_init', $params);

        $result = array();

        $result['globalId']       = $apiResult['no'];
        $result['hashId']         = $file['hashId'];
        $result['outerId']        = $file['id'];
        $result['uploadMode']     = $apiResult['uploadMode'];
        $result['uploadUrl']      = $apiResult['uploadUrl'];
        $result['uploadProxyUrl'] = '';
        $result['uploadToken']    = $apiResult['uploadToken'];

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
            'extno'  => $file['id'],
            'size'   => $initParams['fileSize'],
            'name'   => $initParams['fileName'],
            'hash'   => $initParams['hash']
        );

        $api       = CloudAPIFactory::create('root');
        $apiResult = $api->post("/resources/{$file['globalId']}/upload_resume", $params);

        if (empty($apiResult['resumed']) || ($apiResult['resumed'] !== 'ok')) {
            return null;
        }

        $result = array();

        $result['globalId'] = $file['globalId'];
        $result['outerId']  = $file['id'];
        $result['hashId']   = $file['hashId'];
        $result['resumed']  = $apiResult['resumed'];

        $result['uploadMode']     = $apiResult['uploadMode'];
        $result['uploadUrl']      = $apiResult['uploadUrl'];
        $result['uploadProxyUrl'] = '';
        $result['uploadToken']    = $apiResult['uploadToken'];

        return $result;
    }

    public function download($globalId)
    {
        $api      = CloudAPIFactory::create('leaf');
        $download = $api->get("/resources/{$globalId}/download");
        return $download;
    }

    public function getDownloadFile($file)
    {
        $api              = CloudAPIFactory::create('leaf');
        $download         = $api->get("/resources/{$file['globalId']}/download");
        $download['type'] = 'url';
        return $download;
    }

    public function getDefaultHumbnails($globalId)
    {
        if (empty($globalId)) {
            return array();
        }

        $api    = CloudAPIFactory::create('root');
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
        return $api->get("/resources/data/statistics", $options);
    }

    public function findFiles($files, $conditions)
    {
        if (empty($files)) {
            return array();
        }

        $conditions['nos'] = ArrayToolkit::column($files, 'globalId');
        $conditions['nos'] = implode(",", array_unique($conditions['nos']));

        $api    = CloudAPIFactory::create('root');
        $result = $api->get("/resources", $conditions);

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
            throw $this->createServiceException("文件不存在(global id: #{$params['globalId']})，完成上传失败！");
        }

        $params = array(
            "length" => $params['length'],
            'name'   => $params['filename'],
            'size'   => $params['size'],
            'extno'  => $file['id']
        );
        if ($file['targetType'] == 'attachment') {
            $params['type'] = $file['targetType'];
        }
        $api                     = CloudAPIFactory::create('root');
        $result                  = $api->post("/resources/{$file['globalId']}/upload_finish", $params);
        $file                    = $api->get("/resources/{$file['globalId']}", array("refresh" => true));
        $result['convertStatus'] = 'none';
        $result['length']        = $file['length'];
        return $result;
    }

    public function search($conditions)
    {
        $api        = CloudAPIFactory::create('root');
        $url        = '/resources?'.http_build_query($conditions);
        $result     = $api->get($url);
        $cloudFiles = $result['data'];

        $cloudFiles   = ArrayToolkit::index($cloudFiles, 'no');
        $localFileIds = ArrayToolkit::column($cloudFiles, 'extno');

        $localFiles  = $this->getUploadFileDao()->findFilesByIds($localFileIds);
        $localFiles  = ArrayToolkit::index($localFiles, 'globalId');
        $mergedFiles = array();

        foreach ($cloudFiles as $i => $cloudFile) {
            $localFile       = empty($localFiles[$cloudFile['no']]) ? null : $localFiles[$cloudFile['no']];
            $mergedFiles[$i] = $this->mergeCloudFile($localFile, $cloudFile);
        }

        $result['data'] = $mergedFiles;

        return $result;
    }

    private function mergeCloudFile($localFile, $cloudFile)
    {
        if (empty($localFile)) {
            $localFile = array(
                'id'        => 0,
                'storage'   => 'cloud',
                'globalId'  => $cloudFile['no'],
                'usedCount' => 0,
                'hashId'    => $cloudFile['reskey'],
                'fileSize'  => $cloudFile['size'],
                'filename'  => $cloudFile['name']
            );
        }

        unset($cloudFile['id']);

        $file = array_merge($localFile, $cloudFile);
        $file = $this->proccessConvertStatus($file);
        $file = $this->proccessConvertParamsAndMetas($file);

        $file['storage'] = 'cloud';

        return $file;
    }

    public function synData($conditions)
    {
        $files = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime', 'DESC'), 0, 100);

        if (!empty($files)) {
            $api      = CloudAPIFactory::create('root');
            $syncData = $api->post("/resources/data/sync", $files);

            foreach ($syncData as $key => $value) {
                $this->getUploadFileDao()->updateFile($key, array('globalId' => $value));
            }
        }

        return true;
    }

    protected function getFileFullName($file)
    {
        $diskDirectory = $this->getFilePath($file['targetType'], $file['targetId']);
        $filename .= "{$file['hashId']}.{$file['ext']}";

        return $diskDirectory.$filename;
    }

    protected function getVideoWatermarkImages()
    {
        $setting = $this->getSettingService()->get('storage', array());

        if (empty($setting['video_embed_watermark_image']) || ($setting['video_watermark'] != 2)) {
            return array();
        }

        $videoWatermarkImage = $this->getEnvVariable('baseUrl').$this->getKernel()->getParameter('topxia.upload.public_url_path')."/".$setting['video_embed_watermark_image'];
        $pathinfo            = pathinfo($videoWatermarkImage);

        $images = array();
        $heighs = array('240', '360', '480', '720', '1080');

        foreach ($heighs as $height) {
            $images[$height] = "{$pathinfo['dirname']}/{$pathinfo['filename']}-{$height}.{$pathinfo['extension']}";
        }

        return $images;
    }

    protected function getFilePath($targetType, $targetId)
    {
        $diskDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        $subDir        = DIRECTORY_SEPARATOR.$file['targetType'].DIRECTORY_SEPARATOR;
        $subDir .= "{$file['targetType']}-{$file['targetId']}".DIRECTORY_SEPARATOR;

        return $diskDirectory.$subDir;
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

    protected function getCloudClient()
    {
        if (empty($this->cloudClient)) {
            $factory           = new CloudClientFactory();
            $this->cloudClient = $factory->createClient();
        }

        return $this->cloudClient;
    }

    protected function proccessConvertStatus($file)
    {
        if (!isset($file['processStatus'])) {
            return $file;
        }

        $statusMap = array(
            'none'       => 'none',
            'waiting'    => 'waiting',
            'processing' => 'doing',
            'ok'         => 'success',
            'error'      => 'error'
        );

        $file['convertStatus'] = $statusMap[$file['processStatus']];

        return $file;
    }

    protected function proccessConvertParamsAndMetas($file)
    {
        $file['convertParams'] = array();
        $file['metas2']        = array();

        if (!empty($file['directives']['output'])) {
            if ($file['type'] == 'video') {
                $file['convertParams'] = array(
                    'convertor'    => 'HLSEncryptedVideo',
                    'videoQuality' => isset($file['directives']['videoQuality']) ? $file['directives']['videoQuality'] : 'normal',
                    'audioQuality' => isset($file['directives']['audioQuality']) ? $file['directives']['audioQuality'] : 'normal'
                );

                if (isset($file['metas']['levels'])) {
                    foreach ($file['metas']['levels'] as $key => $value) {
                        $value['type']                 = $key;
                        $value['cmd']['hlsKey']        = $file['metas']['levels'][$key]['hlsKey'];
                        $file['metas']['levels'][$key] = $value;
                    }

                    $file['metas2'] = $file['metas']['levels'];
                }

                if (isset($file['directives']['watermarks'])) {
                    $file['convertParams']['hasVideoWatermark'] = 1;
                }
            } elseif (in_array($file['type'], array('ppt', 'document'))) {
                $file['convertParams'] = array(
                    'convertor' => $file['directives']['output']
                );
                $file['metas2'] = $file['metas'];
            } elseif ($file['type'] == 'audio') {
                $file['convertParams'] = array(
                    'convertor'    => $file['directives']['output'],
                    'videoQuality' => 'normal',
                    'audioQuality' => 'normal'
                );
                $file['metas2'] = $file['metas']['levels'];
            }
        }

        return $file;
    }

    protected function getConvertor($name)
    {
        return ConvertorFactory::create($name, $this->getCloudClient(), $this->getKernel()->getParameter('cloud_convertor'));
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}
