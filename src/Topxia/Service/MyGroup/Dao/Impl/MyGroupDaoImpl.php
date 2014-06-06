<?php

namespace Topxia\Service\MyGroup\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\MyGroup\Dao\MyGroupDao;

class MyGroupDaoImpl extends BaseDao implements MyGroupDao {

    protected $table = 'groups';
    private $serializeFields = array(
        'tagIds' => 'json',
    );
    public function getAllgroupinfo($condtion,$sort,$start,$limit){
        $this->filterStartLimit($start, $limit);
        $con=$this->getcondition($condtion);
        $sql = "SELECT groups.*,user.nickname,user.id as userid FROM groups,user where  groups.ownerId=user.id {$con} ORDER BY {$sort} LIMIT $start,$limit";
        return $this->getConnection()->fetchAll($sql) ? : array();
    }
    public function getAllgroupCount($condtion){
       
        
        $con=$this->getcondition($condtion);
        $sql = "SELECT count(groups.id) FROM groups,user where  groups.ownerId=user.id {$con} ";
        return $this->getConnection()->fetchColumn($sql) ? : 0;
    }
    private function getcondition($condtion){
        $con="";
        if(isset($condtion['status'])){
            if($condtion['status']=='on')
            {
            $con.="and groups.status=1 ";
            }
            elseif($condtion['status']=='off'){
                $con.="and groups.status=0 ";

            }
            
        }
        if(isset($condtion['nickname']))
        {
           $nickname='\'%'.$condtion['nickname'].'%\'';
           $con.="and user.nickname like".$nickname; 
        }

        if(isset($condtion['title']))
        {
            $title='\'%'.$condtion['title'].'%\'';
            $con.="and groups.title like".$title; 
        }
        if(isset($condtion['id']))
        {            
            $id='\' '.$condtion['id'].'\'';
            $con.="and groups.id=".$id; 
        }
        return $con;
    }
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

    public function getNum($id,$condition) {
        $sql = "SELECT {$condition} FROM {$this->table} where status=1 and id=? ";
        return $this->getConnection()->fetchColumn($sql, array($id)) ? : 0;
    }

    public function updatememberNum($id, $type) {
        $memberNum = $this->getNum($id,'memberNum');
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
    public function updatethreadNum($id, $type) {
        $threadNum = $this->getNum($id,'threadNum');
        if ($type == '+') {

            $num = array(
                'threadNum' => $threadNum + 1,
            );
        } else if ($type == '-') {

            $num = array(
                'threadNum' => $threadNum - 1,
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
     public function openGroup($id){
        if($this->getConnection()->update($this->table, array('status'=>1), array('id' => $id))){
            return TRUE;
         }
        else{
            return FALSE;
        }
     }
     public function closeGroup($id){
        if($this->getConnection()->update($this->table, array('status'=>0), array('id' => $id))){
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
