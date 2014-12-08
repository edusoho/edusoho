<?php
namespace Custom\Service\ColumnCourseVote\Impl;
use Custom\Service\ColumnCourseVote\ColumnCourseVoteLogService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
class ColumnCourseVoteLogServiceImpl extends BaseService implements ColumnCourseVoteLogService
{
      public function addColumnCourseVoteLog(array $columnCourseVote){
       $fields = $this->createColumnCourseVoteLogByCourseVote($columnCourseVote);
       $this->getColumnCourseVoteLogDao()->addColumnCourseVoteLog($fields);
      }

      private function createColumnCourseVoteLogByCourseVote($columnCourseVote){
        return array("specialColumnId"=>$columnCourseVote['specialColumnId']
                            ,"columnCourseVoteId"=>$columnCourseVote['id']
                            ,"voteCourseName"=>$columnCourseVote['voteCourseName']
                            ,"userId"=>$columnCourseVote['userId']
                            ,"createdTime"=>time());
      }

  private function _filterCourseFields($fields)
  {
    $fields = ArrayToolkit::filter($fields, array(
      ' specialColumnId' => '',
      'id' => '',
      'voteCourseName' => '',
      'userId' => 0

    ));
    return $fields;
  }

       public function isVoted(array $columnCourseVoteLog){
       	$count = $this->getColumnCourseVoteLogDao()->
       	countVoteLogByUseIdAndColumnIdAndCourseVoteIdAndName($columnCourseVoteLog['userId'],$columnCourseVoteLog['specialColumnId'],$columnCourseVoteLog['id'],$columnCourseVoteLog['voteCourseName']);
          if($count>0)
          {
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