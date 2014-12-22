<?php
namespace Custom\Service\ColumnCourseVote\Impl;

use Custom\Service\ColumnCourseVote\ColumnCourseVoteService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class ColumnCourseVoteServiceImpl extends BaseService implements ColumnCourseVoteService
{

    public function getColumnCourseVote($id)
    {
        return $this->getColumnCourseVoteDao()->getColumnCourseVote($id);
    }


    public function findAllCourseVote($start, $limit)
    {
    	return $this->getColumnCourseVoteDao()->findAllCourseVote($start, $limit);
    }

    public function getAllCourseVoteCount()
    {
        return $this->getColumnCourseVoteDao()->getAllCourseVoteCount();
    }
    public function getColumnCourseVoteBySpecialColumnId($specialColumnId){
          return $this->getColumnCourseVoteDao()->getColumnCourseVoteBySpecialColumnId($specialColumnId);
      }

    public function addColumnCourseVote(array $columnCourseVote)
    {
       
        $columnCourseVote = ArrayToolkit::parts($columnCourseVote, array('specialColumnId','isShow','courseAName','courseBName','voteStartTime','voteEndTime'));
        $columnCourseVote['voteStartTime'] =  strtotime($columnCourseVote['voteStartTime']);
        $columnCourseVote['voteEndTime'] =  strtotime($columnCourseVote['voteEndTime']);
        $columnCourseVote['createdTime'] = time();

        $columnCourseVote = $this->getColumnCourseVoteDao()->addColumnCourseVote($columnCourseVote);

        $this->getLogService()->info('columnCourseVote', 'create', "添加课程对垒");

        return $columnCourseVote;
    }
    public function updateColumnCourseVote($id, array $columnCourseVote)
    {
        $temp = $this->getColumnCourseVote($id);
        if (empty($temp)) {
            throw $this->createServiceException("课程对垒(#{$id})不存在，更新失败！");
        }

        $columnCourseVote = ArrayToolkit::parts($columnCourseVote, array('specialColumnId','isShow','courseAName','courseBName','voteStartTime','voteEndTime'));
        $columnCourseVote['voteStartTime'] =  strtotime($columnCourseVote['voteStartTime']);
        $columnCourseVote['voteEndTime'] =  strtotime($columnCourseVote['voteEndTime']);

        $this->getLogService()->info('columnCourseVote', 'update', "编辑课程对垒(#{$id})");

        return $this->getColumnCourseVoteDao()->updateColumnCourseVote($id, $columnCourseVote);
    }
    //验证是否合法
    private function filterColumnFields(&$column, $relatedColumn = null)
    {
        if (empty($column['name'])) {
            throw $this->createServiceException('标签名不能为空，添加失败！');
        }

        $column['name'] = (string) $column['name'];

        $exclude = $relatedColumn ? $relatedColumn['name'] : null;
        if (!$this->isColumnNameAvalieable($column['name'], $exclude)) {
            throw $this->createServiceException('该标签名已存在，添加失败！');
        }

        return $column;
    }
       public function courseVote(array $columnCourseVote){
        // var_dump($columnCourseVote);
        // exit();
            $voteCourseName = $columnCourseVote['voteCourseName'];
            $voteCourseColumn="" ;
            foreach ($columnCourseVote as $key => $value) {
                if($value==$voteCourseName && $key != 'voteCourseName'){
                    $voteCourseColumn = $key;
                    break;
                }
            }
            if(empty($voteCourseColumn)){
                throw $this->createServiceException("要投票的课程不存在!");
            }
            $countAddColumn="";
            if($voteCourseColumn=='courseAName'){
                $countAddColumn='courseACount';
            }
            elseif($voteCourseColumn=='courseBName'){
                $countAddColumn='courseBCount';
            }
            // $columnCourseVote['countAddColumn']  = $countAddColumn;
            $this->getColumnCourseVoteDao()->updateCourseVoteCountByIdAndVoteCountColumn($columnCourseVote['id'],$countAddColumn);
            // courseVote($columnCourseVote);
       }


  private function getLogService()
        {
            return $this->createService('System.LogService');
        }


        private function getColumnCourseVoteDao()
        {
            return $this->createDao('Custom:ColumnCourseVote.ColumnCourseVoteDao');
        }


}  