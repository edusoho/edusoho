<?php
namespace Custom\WebBundle\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ColumnCourseController extends BaseController
{

    private function crateVote(){
        return array("id"=>11,'specialColumnId'=>1,'courseAName'=>"iiiiii","courseBName"=>"bbbbb","voteCourseName"=>"iiiiii");
    }

    public function courseVoteAction(){
        $user = $this->getCurrentUser();
        // $fields = $request->request->all();
        $fields=$this->crateVote();
        $columnCourseVoteId = $fields['id'];
        $specialColumnId = $fields['specialColumnId'];
        $voteCourseName = $fields['voteCourseName'];
        $userId = $user['id'];
        $fields['userId'] = $userId;
        $flag = $this->getCustomColumnCourseVoteLogService()->isVoted($fields);
      //   var_dump($flag);
      // exit();
        if($flag){
              return $this->createJsonResponse(array('status' => 'false', 'errors'=>'您已经参与过本次投票'));
        }
        //1,投票加一
        //2,log日志增加，但是这里没有必要做事物
        $this->getCustomColumnCourseVoteService()->courseVote($fields);
        $this->getCustomColumnCourseVoteLogService()->addColumnCourseVoteLog($fields);
        return $this->createJsonResponse("感谢您的参与");
    }



  private function getCustomColumnCourseVoteService(){
        return $this->getServiceKernel()->createService('Custom:ColumnCourseVote.ColumnCourseVoteService');
    }
    private function getCustomColumnCourseVoteLogService(){
        return $this->getServiceKernel()->createService('Custom:ColumnCourseVote.ColumnCourseVoteLogService');
    }

}