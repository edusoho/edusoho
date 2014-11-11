<?php

namespace Topxia\Service\File\Dao;

interface UploadFileShareDao {
	public function findRecentContacts($sourceUserId);
	
	public function findMySharingContacts($sourceUserId);
	
	public function findShareHistoryByUserId($sourceUserId);
	
	public function findShareHistory($sourceUserId, $targetUserId);
	
	public function createShare($share);
	
	public function updateShare($id, $share);
}