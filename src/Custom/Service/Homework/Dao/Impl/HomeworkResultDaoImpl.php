<?php

namespace Custom\Service\Homework\Dao\Impl;

use Custom\Service\Homework\Dao\HomeworkResultDao;
use Homework\Service\Homework\Dao\Impl\HomeworkResultDaoImpl as BaseHomeworkResultDao;

class HomeworkResultDaoImpl extends BaseHomeworkResultDao implements HomeworkResultDao
{
    protected $table = 'homework_result';

    public function findPairReviewableIds($homework,$userId){
        $sql = "SELECT id FROM {$this->table} WHERE  courseId = ? ORDER BY number ASC";
        return $this->getConnection()->executeQuery($sql, array($courseId))->fetchAll(\PDO::FETCH_COLUMN);
    }
}