<?php

namespace Topxia\Service\MyGroup\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\MyGroup\Dao\GroupMemberDao;

class GroupMemberDaoImpl extends BaseDao implements GroupMemberDao {

    protected $table = 'groups_member';

    public function joinGroup($id, $memberId) {
        if ($this->ismember($id, $memberId)) {
            return 0;
        }
        $joingroup = array(
            'groupId' => $id,
            'memberId' => $memberId,
            'createdTime' => time(),
        );
        $affected = $this->getConnection()->insert($this->table, $joingroup);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }
        return $this->getConnection()->lastInsertId();
    }

    public function exitGroup($id, $memberId) {
        return $this->getConnection()->delete($this->table, array('groupId' => $id, 'memberId' => $memberId));
    }

    public function ismember($id, $userid) {
        $sql = "SELECT id FROM {$this->table} where groupId=? and memberId=?";
        $getid = $this->getConnection()->fetchColumn($sql, array($id, $userid)) ? : 0;
        if ($getid) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function searchjoinGroup($condtion, $start, $limit, $sort) {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT groupId FROM {$this->table} where  memberId=? ORDER BY {$sort} LIMIT $start,$limit";
        return $this->getConnection()->fetchAll($sql, array($condtion['ownerId'])) ? : array();
    }
    //获取最近加入的成员
    public function getgroupmember_recentlyinfo($id){
        $sql="SELECT memberId FROM {$this->table} WHERE groupId=? ORDER BY createdTime DESC LIMIT 0,8";
        return $this->getConnection()->fetchAll($sql,array($id)) ? : array();
    }
    //获取小组成员
    public function getgroupmember_info($id){
        $sql="SELECT memberId FROM {$this->table} WHERE groupId=? ";
        return $this->getConnection()->fetchAll($sql,array($id)) ? : array();
    }
     public function getNum($groupid,$memberid,$condition) {
        $sql = "SELECT {$condition} FROM {$this->table} where  groupId=? and memberId=? ";
        return $this->getConnection()->fetchColumn($sql, array($groupid,$memberid)) ? : 0;
    }
    public function updatethreadNum($groupid,$memberid,$type){
        $threadNum = $this->getNum($groupid,$memberid,'threadNum');
        if ($type == '+') {

            $num = array(
                'threadNum' => $threadNum + 1,
            );
        } else if ($type == '-') {

            $num = array(
                'threadNum' => $threadNum - 1,
            );
        }

        $this->getConnection()->update($this->table, $num, array('groupId' => $groupid,'memberId'=>$memberid));
        return $num;
    }

}
