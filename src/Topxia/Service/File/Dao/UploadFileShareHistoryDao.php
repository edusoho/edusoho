<?php

namespace Topxia\Service\File\Dao;

interface UploadFileShareHistoryDao
{
	public function getShareHistory($id);

	public function addShareHistory($fileShareHistoryFields);

	public function findShareHistoryByUserId($sourceUserId);

	public function searchShareHistoryCount($conditions);

	public function searchShareHistories($conditions, $orderBy, $start, $limit);
}