<?php

namespace Topxia\Service\File\Dao;

interface UploadFileShareDao {

    public function getShare($id);

    public function findSharesByTargetUserIdAndIsActive($targetUserId, $active = 1);

    public function findSharesBySourceUserId($sourceId);

    public function findShareBySourceIdAndTargetId($sourceId, $targetId);

    


	public function findShareHistoryByUserId($sourceUserId);
	
	public function findShareHistory($sourceUserId, $targetUserId);
	
	public function addShare($share);
	
	public function updateShare($id, $share);
}