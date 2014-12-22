<?php

namespace Custom\Service\ColumnCourseVote\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\ColumnCourseVote\Dao\ColumnCourseVoteDao;

class ColumnCourseVoteDaoImpl extends BaseDao implements ColumnCourseVoteDao
{
    protected $table = 'column_course_vote';

    

    public function getColumnCourseVote($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }


    public function getColumnCourseVoteBySpecialColumnId($specialColumnId)
    {
        $sql = "SELECT * FROM {$this->table} where specialColumnId=?";
        return $this->getConnection()->fetchAll($sql, array($specialColumnId));
    }

    public function addColumnCourseVote(array $columnCourseVote)
    {
        $affected = $this->getConnection()->insert($this->table, $columnCourseVote);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Column error.');
        }
        return $this->getColumnCourseVote($this->getConnection()->lastInsertId());
    }

    public function updateColumnCourseVote($id, array $columnCourseVote){
       // var_dump($columnCourseVote);
       // exit();
        $this->getConnection()->update($this->table, $columnCourseVote, array('id' => $id));
        return $this->getColumnCourseVote($id);
    }

       public function updateCourseVoteCountByIdAndVoteCountColumn($id,$countColumn)
       {
            $sql = " UPDATE {$this->table} SET {$countColumn}={$countColumn}+1 WHERE id={$id} ";
            $this->getConnection()->exec($sql);
       }


    public function findAllCourseVote($start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array());
    }


    public function getAllCourseVoteCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} ";
        return $this->getConnection()->fetchColumn($sql, array());
    }

}