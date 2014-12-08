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
        
    }

    public function courseVote(){
        $user = $this->getCurrentUser();
        $fields = $request->request->all();
        $columnCourseVoteId = $fields['columnCourseVoteId'];
        $specialColumnId = $fields['specialColumnId'];
        $voteCourseName = $fields['voteCourseName'];
        $useId = $user['id'];
        $flag = $this->getCustomColumnCourseVoteLogService()->isVoted($fields);
        if(flag){
              return $this->createJsonResponse(array('status' => 'false', 'errors'=>'您已经参与过本次投票'));
        }
        //1,投票加一
        //2,log日志增加，但是这里没有必要做事物
        $this->getCustomColumnCourseVoteService()->courseVote($fields);
        $this->getCustomColumnCourseVoteLogService()->addColumnCourseVoteLog($fields);
        return $this->createJsonResponse("感谢您的参与");
    }



  private function getCustomColumnCourseVoteService(){
        return $this->getServiceKernel()->crateService('Custom:ColumnCourseVote.ColumnCourseVoteService');
    }
    private function getCustomColumnCourseVoteLogService(){
        return $this->getServiceKernel()->crateService('Custom:ColumnCourseVote.ColumnCourseVoteLogService');
    }

}