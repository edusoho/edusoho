<?php
namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor2;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class CloudFileImplementor2Impl extends BaseService implements FileImplementor2
{
    public function getFile($file)
    {
        $api = CloudAPIFactory::create();
        $cloudFile = $api->get("/files/{$file['globalId']}");

        $file['hashId'] = $cloudFile['storageKey'];
        $file['fileSize'] = $cloudFile['size'];

        $statusMap = array(
            'none' => 'none',
            'waiting' => 'waiting',
            'processing' => 'doing',
            'ok' => 'success',
            'error' => 'error',
        );
        $file['convertStatus'] = $statusMap[$cloudFile['processStatus']];

        if ($file['type'] == 'video') {
            $file['convertParams'] = array(
                'convertor' => $cloudFile['processParams']['output'],
                'videoQuality' => $cloudFile['processParams']['videoQuality'],
                'audioQuality' => $cloudFile['processParams']['audioQuality'],
            );
            $file['metas2'] = $cloudFile['metas']['levels'];
        } elseif ($file['type'] == 'ppt') {
            
        } elseif ($file['type'] == 'audio') {
            $file['convertParams'] = array(
                'convertor' => $cloudFile['processParams']['output'],
                'videoQuality' => 'normal',
                'audioQuality' => 'normal',
            );
            $file['metas2'] = $cloudFile['metas']['levels'];
        }
        // echo "<pre>";var_dump($cloudFile, $file); echo "</pre>";exit();

        return $file;
    }

    public function resumeUpload($hash, $file)
    {
        $params = array(
            'bucket' => $file['bucket'],
            'fileName' => $file['filename'],
            'fileSize' => $file['fileSize'],
            'uploadCallback' => empty($file['uploadCallback']) ? '' : $file['uploadCallback'],
            'processParams' => empty($file['processParams']) ? '' : $file['processParams'],
            'extras' => empty($file['extras']) ? '' : $file['extras'],
        );

        $api = CloudAPIFactory::create();
        $resumed = $api->post("/files/{$hash}/resume_upload", $params);
        if (empty($resumed['resumed']) || ($resumed['resumed'] !== 'ok')) {
            return null;
        }

        return $resumed;
    }

    public function prepareUpload($params)
    {
        $file = array();
        $file['filename'] = empty($params['fileName']) ? '' : $params['fileName'];

        $pos = strrpos($file['filename'], '.');
        $file['ext'] = empty( $pos ) ? '' : substr($file['filename'], $pos+1);

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
        $file['hashId'] = uniqid('NIL') . date('Yndhis');
        $file['etag'] = $file['hashId'];
        $file['convertHash'] = $file['hashId'];
        
        return $file;
    }

    public function initUpload($file)
    {
        $params = array(
            'outerId' => $file['id'],
            'bucket' => $file['bucket'],
            'fileName' => $file['filename'],
            'fileSize' => $file['fileSize'],
            'hash' => $file['hash'],
            'uploadCallback' => empty($file['uploadCallback']) ? '' : $file['uploadCallback'],
            'processParams' => empty($file['processParams']) ? '' : $file['processParams'],
            'extras' => empty($file['extras']) ? '' : $file['extras'],
        );

        $api = CloudAPIFactory::create();
        return $api->post('/files/init_upload', $params);
    }
}