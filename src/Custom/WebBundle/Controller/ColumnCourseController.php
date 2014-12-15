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

    public function indexAction(Request $request, $id){
        $column=$this->getColumnService()->getColumn($id);
        $votedCourse = $this->getCustomColumnCourseVoteService()->getColumnCourseVoteBySpecialColumnId($column['id']);

        $lowOptions = array("complexity"=>'lowLevel');
        $lowCount = $this->getCustomCourseSearcheService()->searchCourseCount($lowOptions);
        $lowCourses = $this->getCustomCourseSearcheService()->searchCourses(
            $lowOptions, null,
            0,
           $lowCount
        );

        $middleOptions = array("complexity"=>'middleLevel');
        $middleCount = $this->getCustomCourseSearcheService()->searchCourseCount($middleOptions);
        $middleCourses = $this->getCustomCourseSearcheService()->searchCourses(
            $middleOptions, null,
            0,
           $middleCount
        );

        $highOptions = array("complexity"=>'highLevel');
        $highCount = $this->getCustomCourseSearcheService()->searchCourseCount($highOptions);
        $highCourses = $this->getCustomCourseSearcheService()->searchCourses(
            $highOptions, null,
            0,
           $highCount
        );

        $OnlineOptions = array("categoryId"=>$this->getOnlineCategoryId());
        $OnlineCount = $this->getCustomCourseSearcheService()->searchCourseCount($OnlineOptions);
        $OnlineCourses = $this->getCustomCourseSearcheService()->searchCourses(
            $OnlineOptions, null,
            0,
           $OnlineCount
        );
       
        return $this->render('TopxiaWebBundle:Column:show.html.twig', array(
            'column'=>$column,
            'votedCourse'=> $votedCourse,
            'lowCount'=> $lowCount,
            'lowCourses'=> $lowCourses,
            'middleCount'=>$middleCount,
            'middleCourses'=>$middleCourses,
            'highCount'=>$highCount,
            'highCourses'=>$highCourses,
            'OnlineCount'=>$OnlineCount,
            'OnlineCourses'=>$OnlineCourses,
        ));

    }

    private function getCourseVoteBySpecialColumnId($ColumnId){
        return $this->getCustomColumnCourseVoteService()->getColumnCourseVoteBySpecialColumnId($ColumnId);
    }

    private function getOnlineCategoryId(){
            $category = $this->getCategoryService()->getCategoryByCode('online');
            return  empty($category) ? -1 : $category['id'];
    }


    public function courseVoteAction(Request $request){
        $user = $this->getCurrentUser();
        $fields = $request->query->all();
     
        $columnCourseVoteId = $fields['id'];
        $specialColumnId = $fields['specialColumnId'];
        $voteCourseName = $fields['voteCourseName'];
        $userId = $user['id'];
        $fields['userId'] = $userId;
        $flag = $this->getCustomColumnCourseVoteLogService()->isVoted($fields);
        if($flag){
              return $this->createJsonResponse(array('status' => 'false', 'errors'=>'您已经参与过本次投票'));
        }
        //1,投票加一
        //2,log日志增加，但是这里没有必要做事物
        $this->getCustomColumnCourseVoteService()->courseVote($fields);
        $this->getCustomColumnCourseVoteLogService()->addColumnCourseVoteLog($fields);
        return $this->createJsonResponse("感谢您的参与");
    }



    private function getColumnService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
    }
    private function getCustomColumnCourseVoteService(){
        return $this->getServiceKernel()->createService('Custom:ColumnCourseVote.ColumnCourseVoteService');
    }
    private function getCustomColumnCourseVoteLogService(){
        return $this->getServiceKernel()->createService('Custom:ColumnCourseVote.ColumnCourseVoteLogService');
    }
    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
    private function getCustomCourseSearcheService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
    }

}