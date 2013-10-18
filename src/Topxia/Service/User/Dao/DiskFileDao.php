<?php

namespace Topxia\Service\User\Dao;

interface DiskFileDao
{
	public function getFile($id);

	public function getFileByConvertHash($hash);

	public function findFilesByIds($ids);

	public function searchFiles($conditions, $sort, $start, $limit);

	public function searchFileCount($conditions);

    public function addFile(array $file);

    public function deleteFile($id);

    public function updateFile($id, array $fields);
}