<?php

namespace Topxia\Service\Activity\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Activity\Dao\ActivityDao;

class ActivityDaoImpl extends BaseDao implements ActivityDao
{
    protected $table = 'activity';

    public function getActivity($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
    
    public function findActivitysByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function searchActivitys($conditions, $orderBy, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

       
          
        return $builder->execute()->fetchAll() ? : array(); 
    }


    public function searchActivityCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    private function _createSearchQueryBuilder($conditions)
    {

        if (isset($conditions['title'])) {
            $conditions['titleLike'] = "%{$conditions['title']}%";
            unset($conditions['title']);
        }

        if (isset($conditions['locationId'])) {
            $locationId = (int) $conditions['locationId'];
            if (!empty($locationId)) {
                //$conditions['locationIdLike'] = rtrim($conditions['locationId'], '0') . '%';
                $conditions['locationId']=$locationId;
            }
            //unset($conditions['locationId']);
        }

        if (isset($conditions['tags'])) {
            $tags = (int) $conditions['tags'];
            if (!empty($tagId)) {
              //  $conditions['tagsLike'] = "%|{$conditions['tagId']}|%";
                $conditions['tagsLike'] = $tags;

            }
           unset($conditions['tags']);
        }

        if(empty($conditions['startTimeGreaterThan'])){
           unset($conditions['startTimeGreaterThan']);
        }

        if(empty($conditions['startTimeGreaterThan'])){
           unset($conditions['startTimeGreaterThan']);
        }



        if(empty($conditions['status'])){
            unset($conditions['status']);       
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'activity')
            ->andWhere('status = :status')
            ->andWhere('title LIKE :titleLike')
            ->andWhere('userId = :userId')
            ->andWhere('city = :city')
            ->andWhere('actType = :actType')
            ->andWhere('tags = :tagsLike')
            ->andWhere('startTime >= :startTimeGreaterThan')
            ->andWhere('startTime < :startTimeLessThan')
            ->andWhere('expired = :expired')
            ->andWhere('recommended = :recommended');
    }

    public function addActivity($course)
    {
        $affected = $this->getConnection()->insert($this->table, $course);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course error.');
        }
        return $this->getActivity($this->getConnection()->lastInsertId());
    }

    public function updateActivity($id, $fields)
    {
        $count = $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $count>0?$this->getActivity($id):null;
    }

    public function deleteActivity($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }
}