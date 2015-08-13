<?php

namespace Custom\Service\Homework\Dao\Impl;

use Custom\Service\Homework\Dao\HomeworkResultDao;
use Homework\Service\Homework\Dao\Impl\HomeworkDaoImpl;
use Homework\Service\Homework\Dao\Impl\HomeworkResultDaoImpl as BaseHomeworkResultDao;

class HomeworkResultDaoImpl extends BaseHomeworkResultDao implements HomeworkResultDao
{
    protected $table = 'homework_result';
    protected $homework_table = "homework";
    protected $homework_review_table = "homework_review";

    public function findPairReviewables($homework,$userId){
        $sql = "select r.* from {$this->table} r ".
                        " left join {$this->homework_table} w on r.homeworkId=w.id ".
                        " where r.homeworkId=? ".
                        " and r.userId != ? ".
                        " and r.teacherScore is null ".
                        " and r.id not in (select v.homeworkResultId from {$this->homework_review_table} v where v.homeworkId=? and v.userId=?) order by r.pairReviews";
        return $this->getConnection()->fetchAll($sql, array($homework['id'] , $userId, $homework['id'] ,$userId)) ? : array();
    }
}