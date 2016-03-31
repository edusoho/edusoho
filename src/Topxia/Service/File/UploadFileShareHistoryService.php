<?php
namespace Topxia\Service\File;

interface UploadFileShareHistoryService
{
	public function getShareHistory($id);

	public function addShareHistory($sourceUserId, $targetUserId, $isActive);

	public function findShareHistory();
}