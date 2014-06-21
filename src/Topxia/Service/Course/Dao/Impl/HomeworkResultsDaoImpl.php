<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\HomeworkResultsDao;

class HomeworkResultsDaoImpl extends BaseDao implements HomeworkResultsDao
{
    protected $table = 'homework_result';

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

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId)
    {   
        if(empty($courseId) or empty($lessonId)){
            return array();
        }
        $sql = "SELECT * FROM {$this->table} Where courseId = ? And lessonId = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId));
    }

    private function _createSearchQueryBuilder($conditions)
    {   
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'homework_result')
            ->andWhere('userId = :userId')
            ->andWhere('courseId = :courseId')
            ->andWhere('homeworkId = :homeworkId')
            ->andWhere('status = :status');

        return $builder;
    }
}