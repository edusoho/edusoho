<?php

namespace Topxia\Service\File\Dao;

interface UploadFileDao
{
    public function getFile($id);

    public function getFileByHashId($hash);

    public function getFileByGlobalId($globalId);

    public function getFileByConvertHash($hash);

    public function findFilesByIds($ids);

    public function findCloudFilesByIds($ids);

    public function findFilesCountByEtag($etag);

    public function searchFiles($conditions, $sort, $start, $limit);

    public function searchFileCount($conditions);

    public function addFile(array $file);

    public function deleteFile($id);

    public function deleteByGlobalId($globalId);

    public function updateFile($id, array $fields);

    public function waveUploadFile($id, $field, $diff);

    public function getFileByTargetType($targetType);

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds);

    public function getHeadLeaderFiles();
}
