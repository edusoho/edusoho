<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\DiskService;
use Topxia\Common\ArrayToolkit;

class DiskServiceImpl extends BaseService implements DiskService
{   

    public function getFile($id)
    {

    }

    public function getUserFiles($userId, $storage, $path = '/')
    {

    }

    public function addFile(array $file)
    {
    	$diskFile = array();

    	if (!ArrayToolkit::requireds($file, array('filename', 'filepath', 'storage', 'bucket', 'size', 'etag'))) {
    		throw $this->createServiceException('参数缺失，添加用户文件失败!');
    	}

    	$diskFile['filename'] = $file['filename'];
    	$diskFile['filepath'] = $this->filterFilepath($file['filepath']);
    	$diskFile['isDirectory'] = !empty($file['isDirectory']) ? 1 : 0;
    	$diskFile['size'] = (int) $file['size'];
    	$diskFile['mimeType'] = $file['mimeType'];
    	$diskFile['etag'] = $file['etag'];
    	$diskFile['storage'] = $file['storage'];
    	$diskFile['bucket'] = $file['bucket'];
    	$diskFile['type'] = $this->getFileType($diskFile['mimeType']);
    	$diskFile['uri'] = $this->makeFileUri($file);
    	$diskFile['updatedTime'] = $diskFile['createdTime'] = time();

    	return $this->getFileDao()->addFile($diskFile);
    }

    public function renameFile($id, $newFilename)
    {

    }

    public function deleteFile($id)
    {

    }

    private function filterFilepath($filepath)
    {
    	if (empty($filepath)) {
    		return '/';
    	}
    	$filepath = trim($filepath, '/');

    	return '/' . $filepath;
    }

    private function getFileType($mimeType)
    {
    	if (strpos($mimeType, 'video') === 0) {
    		return 'video';
    	} elseif (strpos($mimeType, 'audio') === 0) {
    		return 'audio';
    	} elseif (strpos($mimeType, 'image') === 0) {
    		return 'image';
    	} elseif (strpos($mimeType, 'application/vnd.ms-') === 0 or strpos($mimeType, 'application/vnd.openxmlformats-officedocument') === 0) {
    		return 'document';
    	}

    	return 'other';
    }

    private function makeFileUri($file)
    {
    	$uri = "disk://{$file['storage']}/{$file['bucket']}/{$file['key']}";
    	return $uri;
    }

    private function getFileDao()
    {
        return $this->createDao('User.DiskFileDao');
    }


}