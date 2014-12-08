<?php
namespace Custom\Service\ColumnCourseVote\Impl;
use Custom\Service\ColumnCourseVote\ColumnCourseVoteLogService;
use Topxia\Service\Common\BaseService;
class ColumnCourseVoteLogServiceImpl extends BaseService implements ColumnCourseVoteLogService
{
      public function addColumnCourseVoteLog(array $columnCourseVoteLog){
        $this->getColumnCourseVoteLogDao()->addColumnCourseVoteLog($columnCourseVoteLog);
      }

       public function isVoted(array $columnCourseVoteLog){
       	$count = $this->getColumnCourseVoteLogDao()->
       	countVoteLogByUseIdAndColumnIdAndCourseVoteIdAndName($columnCourseVoteLog['userId'],$columnCourseVoteLog['specialColumnId'],$columnCourseVoteLog['columnCourseVoteId'],$columnCourseVoteLog['voteCourseName']);
    	if($count>0){
    		return true;
    	}else{
    		return  false;
    	}
       }

        private function getColumnCourseVoteLogDao()
        {
            return $this->createDao('Custom:ColumnCourseVote.ColumnCourseVoteLogDao');
        }

}