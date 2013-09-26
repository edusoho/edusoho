<?php
namespace Topxia\Service\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DiskService
{

    public function getFile($id);

    public function getUserFiles($userId, $storage, $path = '/');

    /**
     * 搜索用户空间的文件
     * 
     * @param  array $conditions  搜索条件: userId, type
     * @param  [type] $sort       排序方式: latestUpdated, oldestUpdated, latestCreated, oldestCreated
     * @param  [type] $start      返回文件的开始行数
     * @param  [type] $limit      返回文件的限制行数
     * @return array              符合搜索条件的文件列表
     */
    public function searchFiles($conditions, $sort, $start, $limit);

    public function searchFileCount($conditions);

    public function parseFileUri($uri);

    public function addLocalFile(UploadedFile $originalFile, $userId, $path = '/');

    public function addCloudFile(array $file);

    public function renameFile($id, $newFilename);

    public function deleteFile($id);

}