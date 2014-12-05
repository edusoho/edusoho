<?php

namespace Topxia\Service\Content\Dao;

interface FileDao
{

	public function getFile($id);

    public function getFilesByIds($ids);

	public function findFiles($start, $limit);

	public function findFileCount();

	public function findFilesByGroupId($groupId, $start, $limit);

	public function findFileCountByGroupId($groupId);

	public function addFile($file);

	public function deleteFile($id);

}