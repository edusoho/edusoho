<?php

namespace Custom\Service\ColumnCourseVote\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\ColumnCourseVote\Dao\ColumnCourseVoteLogDao;

class ColumnCourseVoteLogDaoImpl extends BaseDao implements ColumnCourseVoteLogDao
{
    protected $table = 'column_course_vote_log';
    public function addColumnCourseVoteLog(array $columnCourseVoteLog){
         $affected = $this->getConnection()->insert($this->table, $columnCourseVoteLog);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Column error.');
        }
        return $this->getColumnCourseVoteLog($this->getConnection()->lastInsertId());
    }


       public function countVoteLogByUseIdAndColumnIdAndCourseVoteIdAndName($userId,$columnId,$courseVoteId,$courseName){
        $sql = "SELECT count(id) FROM {$this->table} WHERE specialColumnId=?  and columnCourseVoteId=? and voteCourseName=? and userId=?";
        return $this->getConnection->fetchAssoc($sql,array($columnId,$courseVoteId,$courseName,$userId));
       }

     public function getColumnCourseVoteLog($id){
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
     }

}