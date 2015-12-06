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
        $api       = CloudAPIFactory::create();
        $cloudFile = $api->get("/files/{$file['globalId']}");

        return $this->mergeCloudFile($file, $cloudFile);
    }

    public function findFiles($files)
    {
        $globalIds = ArrayToolkit::column($files, 'globalId');
        $api       = CloudAPIFactory::create();
        // $result     = $api->get("/files?ids=".implode(',', $globalIds));
        // $cloudFiles = $result['data'];
        // $cloudFiles = ArrayToolkit::index($cloudFiles, 'id');
        $cloudFiles = array();

        foreach ($files as $i => $file) {
            if (empty($cloudFiles[$file['globalId']])) {
                continue;
            }

            $files[$i] = $this->mergeCloudFile($file, $cloudFiles[$file['globalId']]);
        }

        return $files;
    }

    public function resumeUpload($globalId, $file)
    {
        $params = array(
            'bucket' => $file['bucket'],
            'extno'  => $file['extno'],
            'size'   => $file['size'],
            'name'   => $file['name'],
            'hash'   => $file['hash']
        );

        $api     = CloudAPIFactory::create();
        $resumed = $api->post("/resources/{$globalId}/upload_resume", $params);

        if (empty($resumed['resumed']) || ($resumed['resumed'] !== 'ok')) {
            return null;
        }

        return $resumed;
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
        $file['hashId']      = uniqid('NIL').date('Yndhis');
        $file['etag']        = $file['hashId'];
        $file['convertHash'] = $file['hashId'];

        return $file;
    }

    public function initUpload($file)
    {
        $params = array(
            "extno"  => $file['extno'],
            "bucket" => $file['bucket'],
            "key"    => $file['key'],
            "hash"   => $file['hash'],
            'name'   => $file['name'],
            'size'   => $file['size']
        );

        $api = CloudAPIFactory::create();
        return $api->post('/resources/upload_init', $params);
    }

    public function deleteFile($file)
    {
        if (empty($file['globalId'])) {
            return $this->deleteFileOld($file);
        }
    }

    private function deleteFileOld($file)
    {
        $keys       = array($file['hashId']);
        $keyPrefixs = array();

        foreach (array('sd', 'hd', 'shd') as $key) {
            if (empty($file['metas2'][$key]) || empty($file['metas2'][$key]['key'])) {
                continue;
            }

            // 防错

            if (strlen($file['metas2'][$key]['key']) < 5) {
                continue;
            }

            $keyPrefixs[] = $file['metas2'][$key]['key'];
        }

        if (!empty($file['metas2']['imagePrefix']) && (strlen($file['metas2']['imagePrefix']) > 5)) {
            $keyPrefixs[] = $file['metas2']['imagePrefix'];
        }

        if (!empty($file['metas2']['thumb'])) {
            $keys[] = $file['metas2']['thumb'];
        }

        if (!empty($file['metas2']['pdf']) && !empty($file['metas2']['pdf']['key'])) {
            $keys[] = $file['metas2']['pdf']['key'];
        }

        if (!empty($file['metas2']['swf']) && !empty($file['metas2']['swf']['key'])) {
            $keys[] = $file['metas2']['swf']['key'];
        }

        $result1 = $this->getCloudClient()->deleteFilesByKeys('private', $keys);

        if ($result1['status'] !== 'ok') {
            return false;
        }

        if (!empty($keyPrefixs)) {
            if (!empty($file['convertParams']['convertor']) && $file['convertParams']['convertor'] == 'HLSEncryptedVideo') {
                $result2 = $this->getCloudClient()->deleteFilesByPrefixs('public', $keyPrefixs);
            } else {
                $result2 = $this->getCloudClient()->deleteFilesByPrefixs('private', $keyPrefixs);
            }

            if ($result2['status'] !== 'ok') {
                return false;
            }
        }

        return true;
    }

    public function getDownloadFile($file)
    {
        $api              = CloudAPIFactory::create();
        $download         = $api->get("/files/{$file['globalId']}/download");
        $download['type'] = 'url';
        return $download;
    }

    private function mergeCloudFile($file, $cloudFile)
    {
        $file['hashId']   = $cloudFile['storageKey'];
        $file['fileSize'] = $cloudFile['size'];

        $statusMap = array(
            'none'       => 'none',
            'waiting'    => 'waiting',
            'processing' => 'doing',
            'ok'         => 'success',
            'error'      => 'error'
        );
        $file['convertStatus'] = $statusMap[$cloudFile['processStatus']];

        if (empty($cloudFile['processParams']['output'])) {
            $file['convertParams'] = array();
            $file['metas2']        = array();
        } else {
            if ($file['type'] == 'video') {
                $file['convertParams'] = array(
                    'convertor'    => $cloudFile['processParams']['output'],
                    'videoQuality' => $cloudFile['processParams']['videoQuality'],
                    'audioQuality' => $cloudFile['processParams']['audioQuality']
                );
                $file['metas2'] = $cloudFile['metas']['levels'];
            } elseif ($file['type'] == 'ppt') {
                $file['convertParams'] = array(
                    'convertor' => $cloudFile['processParams']['output']
                );
                $file['metas2'] = $cloudFile['metas'];
            } elseif ($file['type'] == 'document') {
                $file['convertParams'] = array(
                    'convertor' => $cloudFile['processParams']['output']
                );
                $file['metas2'] = $cloudFile['metas'];
            } elseif ($file['type'] == 'audio') {
                $file['convertParams'] = array(
                    'convertor'    => $cloudFile['processParams']['output'],
                    'videoQuality' => 'normal',
                    'audioQuality' => 'normal'
                );
                $file['metas2'] = $cloudFile['metas']['levels'];
            }
        }

        return $file;
    }
}
