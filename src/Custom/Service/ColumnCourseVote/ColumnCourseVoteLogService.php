<?php
namespace Custom\Service\ColumnCourseVote;

interface ColumnCourseVoteLogService
{
       public function addColumnCourseVoteLog(array $columnCourseVoteLog);

       public function isVoted(array $columnCourseVoteLog);
}

