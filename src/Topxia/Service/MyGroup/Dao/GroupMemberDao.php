<?php

namespace Topxia\Service\MyGroup\Dao;

interface GroupMemberDao
{
	public function joinGroup($id,$memberId);
    public function exitGroup($id,$memberId);
    public function ismember($id, $userid);
 	public function searchjoinGroup($condtion, $start, $limit, $sort);
 	public function getgroupmember_recentlyinfo($id);
 	public function getgroupmember_info($id);
 	public function updatethreadNum($groupid,$memberid,$cond);
}