<?php
namespace Custom\Service\ColumnCourseVote\Dao;

interface ColumnCourseVoteLogDao
{
      public function addColumnCourseVoteLog(array $columnCourseVoteLog);

       public function countVoteLogByUseIdAndColumnIdAndCourseVoteIdAndName($userId,$columnId,$courseVoteId,$courseName);

       public function getColumnCourseVoteLog($id);
}
    
}