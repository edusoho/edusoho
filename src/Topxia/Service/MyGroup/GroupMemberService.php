<?php

namespace Topxia\Service\MyGroup;

interface GroupMemberService {

    public function joinGroup($id, $title);
    public function exitGroup($id, $title);
 	public function searchjoinGroup($condtion, $start, $limit, $sort);
    public function ismember($id, $userid);
    public function getgroupmember_recentlyinfo($id);
    public function getgroupmember_info($id);
  
}
