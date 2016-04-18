<?php
namespace Topxia\Service\File\Impl;

use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor2;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudFileImplementor2Impl extends BaseService implements FileImplementor2
{
    public function getFile($file)
    {
        $api       = CloudAPIFactory::create('root');
        $cloudFile = $api->get("/resources/{$file['globalId']}");

        return $this->mergeCloudFile($file, $cloudFile);
    }

    public function player($globalId)
    {
        $api    = CloudAPIFactory::create('leaf');
        $player = $api->get("/resources/{$globalId}/player");
        return $player;
    }

    public function get($globalId)
    {
        $api       = CloudAPIFactory::create('root');
        $cloudFile = $api->get("/resources/".$globalId);
        $localFile = $this->getUploadFileDao()->getFileByGlobalId($globalId);
        return $this->mergeCloudFile2($localFile, $cloudFile);
    }

    public function updateFile($globalId, $fields)
    {
        if (!empty($globalId)) {
            $api       = CloudAPIFactory::create('root');
            $cloudFile = $api->post("/resources/".$globalId, $fields);
            $localFile = $this->getUploadFileDao()->getFileByGlobalId($globalId);
            return $this->mergeCloudFile2($localFile, $cloudFile);
        }

        return false;
    }

    public function deleteFile($file)
    {
        if (!empty($file['globalId'])) {
            $api = CloudAPIFactory::create('root');
            return $api->delete("/resources/{$file['globalId']}");
        }

        return false;
    }

    public function download($globalId)
    {
        $api      = CloudAPIFactory::create('leaf');
        $download = $api->get("/resources/{$globalId}/download");
        return $download;
    }

    public function reconvert($globalId, $options)
    {
        $api = CloudAPIFactory::create('root');
        return $api->post("/resources/{$globalId}/processes", $options);
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
        $result['resumed']  = $apiResult['resumed'];

        $result['uploadMode']     = $apiResult['uploadMode'];
        $result['uploadUrl']      = $apiResult['uploadUrl'];
        $result['uploadProxyUrl'] = '';
        $result['uploadToken']    = $apiResult['uploadToken'];

        return $result;
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

        if (isset($file['directives'])) {
            $params['directives'] = $file['directives'];
        }

#TODO... 暂时直传
        // $params['uploadType'] = 'direct';

        $api       = CloudAPIFactory::create();
        $apiResult = $api->post('/resources/upload_init', $params);

        $result = array();

        $result['globalId'] = $apiResult['no'];
        $result['outerId']  = $file['id'];

        $result['uploadMode']     = $apiResult['uploadMode'];
        $result['uploadUrl']      = $apiResult['uploadUrl']; #'http://upload.edusoho.net';
        $result['uploadProxyUrl'] = '';
        $result['uploadToken']    = $apiResult['uploadToken'];

        return $result;
    }

    public function getDownloadFile($file)
    {
        $api              = CloudAPIFactory::create('leaf');
        $download         = $api->get("/resources/{$file['globalId']}/download");
        $download['type'] = 'url';
        return $download;
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

        $api                     = CloudAPIFactory::create('root');
        $result                  = $api->post("/resources/{$file['globalId']}/upload_finish", $params);
        $file                    = $api->get("/resources/{$file['globalId']}", array("refresh" => true));
        $result['convertStatus'] = 'none';
        $result['length']        = $file['length'];
        return $result;
    }

    public function search($conditions)
    {
        $api          = CloudAPIFactory::create('root');
        $url          = '/resources?'.http_build_query($conditions);
        $result       = $api->get($url);
        $cloudFiles   = $result['data'];
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

    private function mergeCloudFile($file, $cloudFile)
    {
        $file['hashId']        = $cloudFile['reskey'];
        $file['fileSize']      = $cloudFile['size'];
        $file['views']         = $cloudFile['views'];
        $file['tags']          = $cloudFile['tags'];
        $file['thumbnail']     = $cloudFile['thumbnail'];
        $file['description']   = $cloudFile['description'];
        $file['processStatus'] = $cloudFile['processStatus'];
        $file['type']          = $cloudFile['type'];
        $file['filename']      = $cloudFile['name'];
        $file['createdTime']   = $cloudFile['createdTime'];
        $file['no']            = $cloudFile['no'];
        $statusMap             = array(
            'none'       => 'none',
            'waiting'    => 'waiting',
            'processing' => 'doing',
            'ok'         => 'success',
            'error'      => 'error'
        );
        $file['convertStatus'] = $statusMap[$cloudFile['processStatus']];

        if (empty($cloudFile['directives']['output'])) {
            $file['convertParams'] = array();
            $file['metas2']        = array();
        } else {
            if ($file['type'] == 'video') {
                $file['convertParams'] = array(
                    'convertor'    => 'HLSEncryptedVideo',
                    'videoQuality' => isset($cloudFile['directives']['videoQuality']) ? $cloudFile['directives']['videoQuality'] : 'normal',
                    'audioQuality' => isset($cloudFile['directives']['audioQuality']) ? $cloudFile['directives']['audioQuality'] : 'normal'
                );

                if (isset($cloudFile['metas']['levels'])) {
                    foreach ($cloudFile['metas']['levels'] as $key => $value) {
                        $value['type']                      = $key;
                        $value['cmd']['hlsKey']             = $cloudFile['metas']['levels'][$key]['hlsKey'];
                        $cloudFile['metas']['levels'][$key] = $value;
                    }

                    $file['metas2'] = $cloudFile['metas']['levels'];
                }
            } elseif ($file['type'] == 'ppt') {
                $file['convertParams'] = array(
                    'convertor' => $cloudFile['directives']['output']
                );
                $file['metas2'] = $cloudFile['metas'];
            } elseif ($file['type'] == 'document') {
                $file['convertParams'] = array(
                    'convertor' => $cloudFile['directives']['output']
                );
                $file['metas2'] = $cloudFile['metas'];
            } elseif ($file['type'] == 'audio') {
                $file['convertParams'] = array(
                    'convertor'    => $cloudFile['directives']['output'],
                    'videoQuality' => 'normal',
                    'audioQuality' => 'normal'
                );
                $file['metas2'] = $cloudFile['metas']['levels'];
            }
        }

        return $file;
    }

//以云端数据为主，字段也一样，以云端为主，只需要合并某些业务字段
    //未来需要替换掉以前的merge方法
    public function mergeCloudFile2($localFile, $cloudFile)
    {
        if ($localFile) {
            $cloudFile['id']            = $localFile['id'];
            $cloudFile['targetType']    = $localFile['targetType'];
            $cloudFile['targetId']      = $localFile['targetId'];
            $cloudFile['createdUserId'] = $localFile['createdUserId'];
            $cloudFile['updatedUserId'] = $localFile['updatedUserId'];
            $cloudFile['isPublic']      = $localFile['isPublic'];
            $cloudFile['usedCount']     = $localFile['usedCount'];
            $cloudFile['ext']           = $localFile['ext'];
        } else {
            //没有本地文件
            $cloudFile['id'] = 0;
        }

        return $cloudFile;
    }

    public function synData($conditions)
    {
        $files    = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime', 'DESC'), 0, 5);
        $api      = CloudAPIFactory::create('root');
        $syncData = $api->post("/resources/data/sync", $files);

        foreach ($syncData as $key => $value) {
            $this->getUploadFileDao()->updateFile($key, array('globalId' => $value));
        }

        return true;
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

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }
}
