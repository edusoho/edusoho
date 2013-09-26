<?php

namespace Topxia\Service\User\Dao;

interface DiskFileDao
{
	public function getFile($id);

	public function searchFiles($conditions, $sort, $start, $limit);

	public function searchFileCount($conditions);

    public function addFile(array $file);
}