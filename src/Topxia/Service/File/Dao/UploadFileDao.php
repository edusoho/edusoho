<?php

namespace Topxia\Service\File\Dao;

interface UploadFileDao
{
	public function getFile($id);

	public function getFileByHashId($hash);

    public function getFileByConvertHash($hash);

	public function findFilesByIds($ids);

    public function findFilesCountByEtag($etag);

	public function searchFiles($conditions, $sort, $start, $limit);

	public function searchFileCount($conditions);

    public function addFile(array $file);

    public function deleteFile($id);

    public function updateFile($id, array $fields);

    public function updateFileUsedCount($fileIds, $offset);

    public function getFileByTargetType($targetType);
}