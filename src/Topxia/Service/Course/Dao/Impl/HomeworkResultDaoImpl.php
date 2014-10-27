<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\HomeworkResultDao;

class HomeworkResultDaoImpl extends BaseDao implements HomeworkResultDao
{
    protected $table = 'homework_result';

    public function getHomeworkResult($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addHomeworkResult(array $fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert HomeworkResult error.');
        }

        return $this->getHomeworkResult($this->getConnection()->lastInsertId());  
    }

    public function updateHomeworkResult($id,array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getHomeworkResult($id);
    }

    public function getHomeworkResultByHomeworkId($homeworkId)
    {
        if (empty($homeworkId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($homeworkId)) ? : null;

    }
    
    public function getHomeworkResultByHomeworkIdAndUserId($homeworkId, $userId)
    {
        if (empty($homeworkId) or empty($userId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($homeworkId, $userId)) ? : null;
    }

    public function getHomeworkResultByHomeworkIdAndStatusAndUserId($homeworkId, $status, $userId)
    {
        if (empty($homeworkId)  or empty($status) or empty($userId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE homeworkId = ? AND status = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($homeworkId, $status, $userId)) ? : null;
    }

    public function getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId)
    {
        if (empty($courseId) or empty($lessonId) or empty($userId)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND lessonId = ?  AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $lessonId, $userId)) ? : null;
    }

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ? : array(); 
    }

    public function searchHomeworkResultsCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function findHomeworkResultsByHomeworkIds($homeworkIds)
    {
        if(empty($homeworkIds)){
            return array();
        }
        $marks = str_repeat('?,', count($homeworkIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE homeworkId IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $homeworkIds);
    }

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId)
    {   
        if(empty($courseId) or empty($lessonId)){
            return array();
        }
        $sql = "SELECT * FROM {$this->table} Where courseId = ? And lessonId = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId));
    }

    public function findHomeworkResultsByCourseIdAndLessonIdAndStatus($courseId, $lessonId,$status)
    {
        if(empty($courseId) or empty($lessonId) or empty($status)){
            return array();
        }
        $sql = "SELECT * FROM {$this->table} Where courseId = ? And lessonId = ? AND status = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId,$status));
    }

    public function findHomeworkResultsByStatusAndCheckTeacherId($status,$checkTeacherId)
    {
        if(empty($checkTeacherId) or empty($status)){
            return array();
        }
        $sql = "SELECT * FROM {$this->table} Where status = ? AND checkTeacherId = ? ";
        return $this->getConnection()->fetchAll($sql, array($status,$checkTeacherId));
    }

    public function findResultsByHomeworkIdAndStatus($homeworkId, $status, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} Where homeworkId = ? And status = ?  LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($homeworkId, $status));
    }

    public function findHomeworkResultsByStatusAndUserId($userId, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND status = ? ";
        return $this->getConnection()->fetchAssoc($sql,array($userId, $status)) ? : null;
    }
    
    private function _createSearchQueryBuilder($conditions)
    {   
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'homework_result')
            ->andWhere('userId = :userId')
            ->andWhere('courseId = :courseId')
            ->andWhere('lessonId = :lessonId')
            ->andWhere('homeworkId = :homeworkId')
            ->andWhere('status = :status')
            ->andWhere('checkTeacherId = :checkTeacherId')
            ->andWhere('commitStatus = :commitStatus');

        return $builder;
    }
}