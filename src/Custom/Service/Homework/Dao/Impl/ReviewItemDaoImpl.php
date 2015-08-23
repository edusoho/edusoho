<?php
namespace Custom\Service\Homework\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Homework\Dao\ReviewItemDao;

class ReviewItemDaoImpl extends BaseDao implements ReviewItemDao
{
    protected $table = 'homework_review_item';
    protected $review_table='homework_review';

    public function create($item){
        $affected = $this->getConnection()->insert($this->table, $item);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert homework review item error.');
        }
        return $this->get($this->getConnection()->lastInsertId());
    }

    public function get($id){
        $sql = "SELECT * FROM {$this->table} WHERE id = ? limit 1";
        return  $this->getConnection()->fetchAssoc($sql,array($id)) ? : array();
    }

    public function averageItemScores($resultId){
        $sql = "SELECT i.homeworkItemResultId,avg(i.score) score FROM {$this->table} i".
            " left join {$this->review_table} r on i.homeworkReviewId=r.id ".
            " where i.homeworkResultId=1 and r.category='student' ".
            " group by i.homeworkItemResultId";
        return $this->getConnection()->fetchAll($sql, array($resultId));
    }

    public function findItemsByResultId($resultId){
        $sql = "SELECT * FROM {$this->table} WHERE homeworkResultId = ?";
        return $this->getConnection()->fetchAll($sql, array($resultId)) ? : null;
    }
}