<?php
namespace Custom\Service\Homework\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Homework\Dao\ReviewDao;

class ReviewItemDaoImpl extends BaseDao implements ReviewItemDao
{
    protected $table = 'homework_review_item';

    // public function create($review){
    //     $affected = $this->getConnection()->insert($this->table, $review);
    //     if ($affected <= 0) {
    //         throw $this->createDaoException('Insert homework review error.');
    //     }
    //     return $this->get($this->getConnection()->lastInsertId());
    // }

    public function get($id){
        $sql = "SELECT * FROM {$this->table} WHERE id = ? limit 1";
        return  $this->getConnection()->fetchAssoc($sql,array($id)) ? : array();
    }
}