<?php

namespace Topxia\Service\Photo\Dao;

interface PhotoFileDao
{

	public function getFile($id);

	public function searchFileCount($conditions);

	public function searchFiles($conditions, $orderBy, $start, $limit);

	public function findFileByIds(array $ids);

	public function findFiles($start, $limit);

	public function addFile($file);

	public function deleteFile($id);

	public function updateFile($id, $fields);


}