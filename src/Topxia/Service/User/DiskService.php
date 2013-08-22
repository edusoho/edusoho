<?php
namespace Topxia\Service\User;

interface DiskService
{

    public function getFile($id);

    public function getUserFiles($userId, $storage, $path = '/');

    public function addFile(array $file);

    public function renameFile($id, $newFilename);

    public function deleteFile($id);

}