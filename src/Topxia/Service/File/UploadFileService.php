<?php
namespace Topxia\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadFileService
{
		public function getFile($id);

		public function getFile2($id);//2

    public function getFileByGlobalId($globalId);

		public function getFileFromLeaf($id);//2

		public function getUploadFileInit($id);//2

		public function getThinFile($id);//2

	  public function getFileByGlobalId2($globalId);//2

		public function findCloudFilesByIds($fileIds);//2

	  public function findThinFilesByIds(array $ids);//2

    public function findFilesByIds2(array $ids);//2

		public function findFilesByTargetTypeAndTargetId($targetType, $targetId);//2

		public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds);//2

		public function searchFiles2($conditions, $orderBy, $start, $limit);//2

		public function searchFilesCount2($conditions);//2

		public function edit($fileId, $fields);//2

		public function getDownloadFile($id);//2

		public function getUploadAuth($params);//2

		public function initUpload($params);//2

		public function finishedUpload($params);//2

		public function moveFile($targetType, $targetId, $originalFile = null, $data);//2

		public function setFileProcessed($params);//2

		public function deleteFile2($id);//2

		public function deleteFiles2(array $ids);//2

		public function deleteByGlobalId($globalId);//2

		public function increaseFileUsedCount($id);//2

		public function decreaseFileUsedCount($id);//2

		public function searchShareHistoryCount($conditions);//2

		public function searchShareHistories($conditions, $orderBy, $start, $limit);//2

		public function findActiveShareHistory($sourceUserId);//2

		public function findMySharingContacts2($targetUserId);//2

		public function reconvertFile2($id, $options = array());//2

		public function collectFile($userId, $fileId);//2

		public function findCollectionsByUserIdAndFileIds($fileIds, $userId);//2

		public function findCollectionsByUserId($userId);//2

		public function getFileByTargetType2($targetType);//2

		public function syncFile($file);//2

    public function getFileByHashId($hashId);

    public function getFileByConvertHash($hash);

    public function findFilesByIds(array $ids);

    public function searchFiles($conditions, $sort, $start, $limit);

    public function searchFileCount($conditions);

    public function addFile($targetType, $targetId, array $fileInfo=array(), $implemtor='local', UploadedFile $originalFile=null);

    public function renameFile($id, $newFilename);

    public function deleteFile($id);

    public function deleteFiles(array $ids);

    public function convertFile($id, $status, array $result = array(), $callback = null);

    public function saveConvertResult($id, array $result = array());

    public function setFileConverting($id, $convertHash);


    public function makeUploadParams($params);

    public function reconvertFile($id, $convertCallback);

    public function reconvertOldFile($id, $convertCallback, $pipeline);

    public function getMediaInfo($key, $type);

    public function getFileByTargetType($targetType);

    public function findMySharingContacts($targetUserId);

    public function shareFiles($sourceUserId, $targetUserIds);

    public function addShare($sourceUserId, $targetUserId);

    public function updateShare($shareHistoryId);

    public function findShareHistoryByUserId($sourceUserId, $targetUserId);

    public function findShareHistory($sourceUserId);

    public function cancelShareFile($sourceUserId, $targetUserId);

    public function waveUploadFile($id, $field, $diff);
}
