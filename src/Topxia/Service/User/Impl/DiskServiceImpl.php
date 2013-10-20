<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\DiskService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoCloudClient;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class DiskServiceImpl extends BaseService implements DiskService
{   

    public function getFile($id)
    {
        return DiskFileSerialize::unserialize($this->getDiskFileDao()->getFile($id));
    }

    public function getFileByConvertHash($hash)
    {
        return $this->getDiskFileDao()->getFileByConvertHash($hash);
    }

    public function findFilesByIds(array $ids)
    {
        return $this->getDiskFileDao()->findFilesByIds($ids);
    }

    public function getUserFiles($userId, $storage, $path = '/')
    {

    }

    public function searchFiles($conditions, $sort, $start, $limit)
    {

        switch ($sort) {
            case 'latestUpdated':
                $orderBy = array('updatedTime', 'DESC');
                break;
            case 'oldestUpdated':
                $orderBy =  array('updatedTime', 'ASC');
                break; 
            case 'latestCreated':
                $orderBy =  array('createdTime', 'DESC');
                break;
            case 'oldestCreated':
                $orderBy =  array('createdTime', 'ASC');
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }

        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getDiskFileDao()->searchFiles($conditions, $orderBy, $start, $limit);
    }

    private function prepareSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (isset($conditions['nickname'])) {
            $user = $this->getUserService()->getUserByNickname($conditions['nickname']);
            if(empty($user)){
                 $conditions['userId'] = 9999999999;
            } else {
                $conditions['userId'] = $user['id'];
                
            }
            unset($conditions['nickname']);
        }

        return $conditions;
    }

    public function searchFileCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);
        return $this->getDiskFileDao()->searchFileCount($conditions);
    }

    public function parseFileUri($uri)
    {
        if (strpos($uri, 'disk://') === false) {
            throw $this->createServiceException('uri error.');
        }

        $result = array();

        $isLocal = preg_match('/disk:\/\/local\/(.*)/', $uri, $maches);
        if ($isLocal) {
            $result['type'] = 'local';
            $result['path'] = $maches[1];
            $result['fullpath'] = $this->getKernel()->getParameter('topxia.disk.local_directory'). '/' . $result['path'];

            return $result;
        }

        $isCloud = preg_match('/disk:\/\/cloud\/(.*?)\/(.*)/', $uri, $maches);
        if ($isCloud) {
            $result['type'] = 'cloud';
            $result['bucket'] = $maches[1];
            $result['key'] = $maches[2];

            return $result;
        }

        $this->createServiceException('URI不正确。');
    }

    public function addLocalFile(UploadedFile $originalFile, $userId, $path = '/')
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createServiceException('用户不存在，上传失败！');
        }

        $diskDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        $disk = new UserLocalDisk($user, $diskDirectory);
        $savedFile = $disk->saveFile($originalFile, $path);
        $diskfile = array();
        $diskFile['userId'] = $this->getCurrentUser()->id;
        $diskFile['filename'] = $originalFile->getClientOriginalName();
        $diskFile['filepath'] = $path;
        $diskFile['isDirectory'] = 0;
        $diskFile['size'] = $savedFile->getSize();
        $diskFile['mimeType'] = $savedFile->getMimeType();
        $diskFile['etag'] = md5_file($savedFile->getPathname());
        $diskFile['storage'] = 'local';
        $diskFile['bucket'] = '';
        $diskFile['type'] = $this->getFileType($diskFile['mimeType']);
        $diskFile['uri'] = 'disk://local/' . substr($savedFile->getPathname(), strlen(realpath($diskDirectory))+1);
        $diskFile['updatedTime'] = $diskFile['createdTime'] = time();

        $diskFile = $this->getDiskFileDao()->addFile($diskFile);

        return $diskFile;
    }

    public function addCloudFile(array $file)
    {
    	$diskFile = array();

    	if (!ArrayToolkit::requireds($file, array('filename', 'filepath', 'storage', 'bucket', 'size', 'etag'))) {
    		throw $this->createServiceException('参数缺失，添加用户文件失败!');
    	}

        $diskFile['userId'] = $this->getCurrentUser()->id;
    	$diskFile['filename'] = $file['filename'];
    	$diskFile['filepath'] = $this->filterFilepath($file['filepath']);
    	$diskFile['isDirectory'] = !empty($file['isDirectory']) ? 1 : 0;
    	$diskFile['size'] = (int) $file['size'];
    	$diskFile['mimeType'] = $file['mimeType'];
    	$diskFile['etag'] = $file['etag'];
        if (empty($file['convertId']) or empty($file['convertKey'])) {
            $diskFile['convertHash'] = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
            $diskFile['convertStatus'] = 'none';
        } else {
            $diskFile['convertHash'] = "{$file['convertId']}:{$file['convertKey']}";
            $diskFile['convertStatus'] = 'waiting';
        }
    	$diskFile['storage'] = $file['storage'];
    	$diskFile['bucket'] = $file['bucket'];
    	$diskFile['type'] = $this->getFileType($diskFile['mimeType']);
    	$diskFile['uri'] = $this->makeFileUri($file);
    	$diskFile['updatedTime'] = $diskFile['createdTime'] = time();

    	$diskFile = $this->getDiskFileDao()->addFile($diskFile);

        $this->getLogService()->info('disk', 'add_cloud_file', json_encode($file));

        return $diskFile;
    }

    public function renameFile($id, $newFilename)
    {

    }

    public function deleteFile($id)
    {
        $file = $this->getFile($id);
        if (empty($file)) {
            throw $this->createServiceException("文件(#{$id})不存在，删除失败");
        }

        return $this->getDiskFileDao()->deleteFile($id);
    }

    public function deleteFiles(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteFile($id);
        }
    }

    public function setFileFormats($id, array $items)
    {
        $cmds = EdusohoCloudClient::getVideoConvertCommands();

        $formats = array();
        foreach ($items as $item) {
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

            $formats[$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);
        }

        if (empty($formats)) {
            $fields = array('convertStatus' => 'error', 'formats' => $formats);
        } else {
            $fields = array('convertStatus' => 'success', 'formats' => $formats);
        }

        return DiskFileSerialize::unserialize(
            $this->getDiskFileDao()->updateFile($id, DiskFileSerialize::serialize($fields))
        );
    }

    public function changeFileConvertStatus($id, $status)
    {
        $statuses = array('none', 'waiting', 'doing', 'success', 'error');
        if (!in_array($status, $statuses)) {
            throw $this->createServiceException('状态不正确，变更文件转换状态失败！');
        }

        $this->getDiskFileDao()->updateFile($id, array('convertStatus' => $status));
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
    	} elseif (strpos($mimeType, 'application/vnd.ms-') === 0 
            or strpos($mimeType, 'application/vnd.openxmlformats-officedocument') === 0
            or strpos($mimeType, 'application/pdf') === 0) {
    		return 'document';
    	}

    	return 'other';
    }

    private function makeFileUri($file)
    {
    	$uri = "disk://{$file['storage']}/{$file['bucket']}/{$file['key']}";
    	return $uri;
    }

    private function getDiskFileDao()
    {
        return $this->createDao('User.DiskFileDao');
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');        
    }

}



class UserLocalDisk
{
    private $diskDirectory;

    private $user;

    public function __construct($user, $diskDirectory)
    {
        if (!is_dir($diskDirectory)) {
            throw new \RuntimeException("{$diskDirectory}目录不存在，请先创建。");
        }

        $this->diskDirectory = $diskDirectory;
        $this->user = $user;
    }

    public function saveFile($file, $path = '/')
    {

        $directory = $this->getUserDirectory($path);
        $ext = $file->guessExtension();
        if ($ext) {
            $ext = $ext == 'jpeg' ? '.jpg' : ".$ext";
        } else {
            $ext = '';
        }

        $filename = '';
        for ($i = 0; $i<10; $i++) {
            $newFilename = time() . '_' . rand(10000, 99999) . $ext;
            if (!file_exists($directory . '/' . $newFilename)) {
                $filename = $newFilename;
                break;
            }
        }

        if (empty($filename)) {
            throw new \RuntimeException('生成文件名失败！');
        }

        return $file->move($directory, $filename);
    }


    private function getUserDirectory($path)
    {
        if (strpos($path, '../')!== false or strpos($path, '..\\') !== false ) {
            throw new \RuntimeException('路径不正确。');
        }
        $userId = $this->user['id'];
        $path = trim($path, '/\\');

        $directory = rtrim("{$this->diskDirectory}/u{$userId}/{$path}", '/\\');

        return $directory;
    }

}

class DiskFileSerialize
{
    public static function serialize(array $file)
    {
        if (isset($file['formats'])) {
            $file['formats'] = !empty($file['formats']) ? $file['formats'] : array();
            $file['formats'] = json_encode($file['formats']);
        }
        return $file;
    }

    public static function unserialize(array $file = null)
    {
        if (empty($file)) {
            return null;
        }
        $file['formats'] = json_decode($file['formats'], true);
        return $file;
    }

    public static function unserializes(array $files)
    {
        return array_map(function($file) {
            return LessonSerialize::unserialize($file);
        }, $files);
    }
}