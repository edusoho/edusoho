<?php
namespace Topxia\Service\File;

interface UploadFileService2
{
    public function getFile($id);

    /**
     * 获得文件基础信息
     */
    public function getThinFile($id);

    public function getFileByGlobalId($globalId);

    public function findCloudFilesByIds($fileIds);

    public function searchFiles($conditions, $orderBy, $start, $limit);

    public function searchFilesByProcessStatus($conditions, $orderBy, $start, $limit);

    public function searchFilesCount($conditions);

    public function getDownloadFile($id);

    public function initUpload($params);

    public function finishedUpload($params);

    public function setFileProcessed($params);

    public function deleteFile($id);

    public function deleteFiles(array $ids);

    public function increaseFileUsedCount($id);

    public function decreaseFileUsedCount($id);

    /**
     * share file
     */
    public function addShare($sourceUserId, $targetUserId);

    public function cancelShareFile($sourceUserId, $targetUserId);

    public function updateShare($shareHistoryId);

    public function findShareHistory($sourceUserId);

    public function searchShareHistoryCount($conditions);

    public function searchShareHistories($conditions, $orderBy, $start, $limit);

    public function findActiveShareHistory($sourceUserId);

    public function findShareHistoryByUserId($sourceUserId, $targetUserId);

    public function waveUploadFile($id, $field, $diff);

    public function reconvertFile($id, $convertCallback);

    public function findMySharingContacts($targetUserId);
    /**
     * collect file
     */
    public function collectFile($userId, $fileId);

    public function getFileByTargetType($targetType);

    public function findCollectionsByUserIdAndFileIds($fileIds, $userId);

    public function findCollectionsByUserId($userId);

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds);

}
