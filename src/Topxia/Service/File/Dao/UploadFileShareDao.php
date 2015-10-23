<?php

namespace Topxia\Service\File\Dao;

interface UploadFileShareDao {
	public function findShareHistoryByUserId($sourceUserId);
	
	public function findShareHistory($sourceUserId, $targetUserId);
	
	public function addShare($share);
	
	public function updateShare($id, $share);
}