<?php

namespace Topxia\Service\MyGroup\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\MyGroup\Dao\MyGroupDao;

class MyGroupDaoImpl extends BaseDao implements MyGroupDao {

    protected $table = 'groups';
    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function addGroup($group) {

        $group = $this->createSerializer()->serialize($group, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $group);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }
        return $this->getConnection()->lastInsertId();
    }

    public function searchGroup($condtion, $start, $limit, $sort) {
        $this->filterStartLimit($start, $limit);
        if ($condtion['ownerId'] !== null) {
            $sql = "SELECT id,title,logo FROM {$this->table} where status=1 and ownerId=? ORDER BY {$sort} LIMIT $start,$limit";
             return $this->getConnection()->fetchAll($sql,array($condtion['ownerId'])) ? : array();
        } else {
            $sql = "SELECT id,title,logo FROM {$this->table} where status=1 ORDER BY {$sort} LIMIT $start,$limit";
            return $this->getConnection()->fetchAll($sql) ? : array();
        }
    }

    public function getGroupinfo($id) {
        $sql = "SELECT * FROM {$this->table} where status=1 and id=? ";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }

    public function getmemberNum($id) {
        $sql = "SELECT memberNum FROM {$this->table} where status=1 and id=? ";
        return $this->getConnection()->fetchColumn($sql, array($id)) ? : 0;
    }

    public function updatememberNum($id, $type) {
        $memberNum = $this->getmemberNum($id);
        if ($type == '+') {

            $num = array(
                'memberNum' => $memberNum + 1,
            );
        } else if ($type == '-') {

            $num = array(
                'memberNum' => $memberNum - 1,
            );
        }

        $this->getConnection()->update($this->table, $num, array('id' => $id));
        return $num;
    }

    public function isowner($id, $userid) {
        $sql = "SELECT ownerId FROM {$this->table} where status=1 and id=? ";
        $getid = $this->getConnection()->fetchColumn($sql, array($id)) ? : 0;
        if ($getid == $userid) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
     public function getownerId($id){
        $sql = "SELECT ownerId FROM {$this->table} where status=1 and id=? ";
        return $this->getConnection()->fetchColumn($sql, array($id)) ? : 0;
     }
      public function updatgroupinfo($id,$condtion){
         if($this->getConnection()->update($this->table, $condtion, array('id' => $id))){
            return TRUE;
         }
        else{
            return FALSE;
        }
     }
     public function updategroupinfo($id,$group){
        if($this->getConnection()->update($this->table, $group, array('id' => $id))){
            return TRUE;
         }
        else{
            return FALSE;
        }
        
     }
}
