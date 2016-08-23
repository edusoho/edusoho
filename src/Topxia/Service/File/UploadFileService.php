<?php
namespace Topxia\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadFileService
{
    public function getFile($id);

    public function getFileByGlobalId($globalId);

    public function getFullFile($id);

    public function getUploadFileInit($id);

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds);

    public function update($fileId, $fields);

    public function getDownloadMetas($id);

    public function getUploadAuth($params);

    public function initUpload($params);

    public function finishedUpload($params);

    public function moveFile($targetType, $targetId, $originalFile = null, $data);

    public function setFileProcessed($params);

    public function deleteByGlobalId($globalId);

    public function reconvertFile($id, $options = array());

    public function reconvertOldFile($id, $convertCallback, $pipeline);

    public function collectFile($userId, $fileId);

    public function findCollectionsByUserIdAndFileIds($fileIds, $userId);

    public function findCollectionsByUserId($userId);

    public function syncFile($file);

    public function getFileByHashId($hashId);

    public function getFileByConvertHash($hash);

    public function findFilesByIds(array $ids, $showCloud);

    public function searchFiles($conditions, $sort, $start, $limit);

    public function searchFileCount($conditions);

    public function addFile($targetType, $targetId, array $fileInfo = array(), $implemtor = 'local', UploadedFile $originalFile = null);

    public function renameFile($id, $newFilename);

    public function deleteFile($id);

    public function deleteFiles(array $ids);

    public function convertFile($id, $status, array $result = array(), $callback = null);

    public function saveConvertResult($id, array $result = array());

    public function setFileConverting($id, $convertHash);

    public function makeUploadParams($params);

    public function getMediaInfo($key, $type);

    public function getFileByTargetType($targetType);

    public function tryManageFile($fileId);

    public function tryManageGlobalFile($globalFileId);

    public function tryAccessFile($fileId);

    public function canManageFile($fileId);

    public function findMySharingContacts($targetUserId);

    public function shareFiles($sourceUserId, $targetUserIds);

    public function addShare($sourceUserId, $targetUserId);

    public function updateShare($shareHistoryId);

    public function findShareHistoryByUserId($sourceUserId, $targetUserId);

    public function findShareHistory($sourceUserId);

    public function findActiveShareHistory($sourceUserId);

    public function cancelShareFile($sourceUserId, $targetUserId);

    public function searchShareHistoryCount($conditions);

    public function searchShareHistories($conditions, $orderBy, $start, $limit);

    public function waveUploadFile($id, $field, $diff);

    //file-used api
    public function createUseFiles($fileIds, $targetId, $targetType, $type);

    public function getUseFile($id);

    public function deleteUseFile($id);

    public function findUseFilesByTargetTypeAndTargetIdAndType($targetType, $targetId, $type);
}
