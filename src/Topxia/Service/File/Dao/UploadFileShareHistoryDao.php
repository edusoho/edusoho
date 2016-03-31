<?php

namespace Topxia\Service\File\Dao;

interface UploadFileShareHistoryDao
{
	public function getShareHistory($id);

	public function addShareHistory($fileShareHistoryFields);
}