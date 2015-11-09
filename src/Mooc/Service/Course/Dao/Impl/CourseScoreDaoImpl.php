<?php

namespace Mooc\Service\Course\Dao\Impl;

use Mooc\Service\Course\Dao\CourseScoreDao;
use Topxia\Service\Common\BaseDao;

class CourseScoreDaoImpl extends BaseDao implements CourseScoreDao
{
	protected $table = 'course_member_score';

    public function getUserScoreByUserIdAndCourseId($userId,$courseId)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE userId = ? AND courseId = ? LIMIT 1";
    	return $this->getConnection()->fetchAssoc($sql,array($userId,$courseId))?:null;

    }

    public function getUserCourseScore($id)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
    	return $this->getConnection()->fetchAssoc($sql,array($id))?:null;
    }

    public function addUserCourseScore($score)
    {
        $affected = $this->getConnection()->insert($this->table, $score);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert score error.');
        }
        return $this->getUserCourseScore($this->getConnection()->lastInsertId());
    }

    public function updateUserCourseScore($id,$score)
    {
        $this->getConnection()->update($this->table, $score, array('id' => $id));
        return $this->getUserCourseScore($id);
    }

    public function findUsersScoreBySqlJoinUser($fields)
    {
        $sql = "SELECT s.* FROM {$this->table} s JOIN user u ON s.userId = u.id WHERE s.courseId = ?";
        $parmaters = array($fields['courseId']);
        if(isset($fields['staffNo']) && !empty($fields['staffNo'])){
            $sql .= " u.staffNo LIKE ? ";
            $parmaters[] = '%'.$fields['staffNo'].'%';
        }
        if(isset($fields['staffNo']) && !empty($fields['staffNo']) && isset($fields['organizationIds']) && !empty($fields['organizationIds'])){
            $sql .= " AND ";
        }
        if(isset($fields['organizationIds']) && !empty($fields['organizationIds']))
        {
            $marks = str_repeat('?,', count($fields['organizationIds']) - 1) . '?';
            $sql .= " u.organizationId IN ({$marks}) ";
            array_map(function($item) use (&$parmaters){
                $parmaters[] = $item;
            }, $fields['organizationIds']);
        }
        $sql .= " ORDER BY u.staffNo ASC ";
        return $this->getConnection()->fetchAll($sql,$parmaters);
    }

    public function findAllMemberScore($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ";
        return $this->getConnection()->fetchAll($sql,array($courseId))?:null;
    }

    public function findUserScoreByIdsAndCourseId($userIds,$courseId)
    {
        if(empty($userIds)) { 
            return array(); 
        }

        $marks = str_repeat('?,', count($userIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE userId IN ({$marks}) AND courseId = ?";
        $parmaters = $userIds;
        $parmaters[] = $courseId;
        return $this->getConnection()->fetchAll($sql,$parmaters)?:null;
    }

    public function searchMemberScoreCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function searchMemberScore($conditions,$orderBy,$start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ? : array(); 
    }

    protected function _createSearchQueryBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)     
            ->andWhere('courseId = :courseId')
            ->andWhere('userId  = :userId')
            ->andWhere('totalScore  >= :standardScore')
            ->andWhere('userId IN ( :userIds )');


        return $builder;
    }
}