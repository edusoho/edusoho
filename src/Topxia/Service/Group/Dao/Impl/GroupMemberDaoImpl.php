<?php

namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\GroupMemberDao;

class GroupMemberDaoImpl extends BaseDao implements GroupMemberDao 
{
    protected $table = 'groups_member';

    public function getMemberByGroupIdAndUserId($groupId,$userId)
    {
        $sql="SELECT * FROM {$this->table} WHERE groupId=? and userId=? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql,array($groupId,$userId)) ? : null;
    }

    public function searchMembersCount($conditions)
    {
        $builder = $this->_createGroupMemberSearchBuilder($conditions)
                         ->select('count(id)');
        return $builder->execute()->fetchColumn(0); 
    }
    
    public function getMember($id)
    {
        $sql="SELECT * FROM {$this->table} WHERE id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql,array($id)) ? : array();
    }

    public function getMembersByUserId($userId)
    {
        $sql="SELECT * FROM {$this->table} WHERE userId=? ";

        return $this->getConnection()->fetchAll($sql,array($userId)) ? : array();

    }

    public function waveMember($id, $field, $diff)
    {
        $fields = array('postNum', 'threadNum');
        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }


    public function addMember($fields) 
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert GroupMember error.');
        }
        return $this->getMember($this->getConnection()->lastInsertId());
    }

    public function updateMember($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getMember($id);
    }

     public function getMembersCountByGroupId($groupId)
     {
        $sql="SELECT count(id) FROM {$this->table} WHERE groupId=?";
        
        return $this->getConnection()->fetchColumn($sql, array($groupId)) ? : 0;
    }


     public function searchMembers($conditions,$orderBy,$start,$limit)
     {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createGroupMemberSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);
        return $builder->execute()->fetchAll() ? : array();  
    }

    public function deleteMember($id)
    {   
        
        $sql ="DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));

    }

     private function _createGroupMemberSearchBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('groupId = :groupId')
            ->andWhere('role = :role')
            ->andWhere('userId = :userId');
        return $builder;
    }

}
