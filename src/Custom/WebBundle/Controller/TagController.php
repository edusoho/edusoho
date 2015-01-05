<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;


class TagController extends BaseController
{
     public function indexAction (Request $request, $tagId)
    {
        if(empty($tagId)){
            $tagId=$tags[0]['id'];
        }
        $tagDetail = $this->getTagService()->getTag($tagId);
        if (empty($tagDetail)) {
            throw $this->createNotFoundException();
        }

        $conditions = array('tagId' => $tagId);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getCustomCourseSearchService()->searchCourseCount($conditions)
            , 10
        );

        $page = $request->query->get('page');
        if( !$page ){$page = 1;}
        // $start = ($page - 1) * 20;
        // $tags = $this->getCustomTagService()->findAllTags($start, 20);
        $temp = $this->getTags($page-1,20);

         

        $courses = $this->getCustomCourseSearchService()->searchCourses($conditions,null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());
      
        return $this->render('TopxiaWebBundle:Tag:tag-index.html.twig', array(
            'tags' => $temp['tags'],
            'tagDetail' => $tagDetail,
            'courses' => $courses,
            'page'=> $temp['nextPage'],
            'paginator'=>$paginator
        ));

    }

    public function pageAction(Request $request,$page){
       
         $temp = $this->getTags($page,20);
         
        return $this->render('TopxiaWebBundle:Tag:tags.html.twig', array(
        'tags' => $temp['tags'],
        'page' => $temp['nextPage']
        ));
    }


    public function indexPageAction(Request $request,$page){
        
        $temp = $this->getTags($page,14);
        return $this->render('TopxiaWebBundle:Tag:index-tag-detail.html.twig', array(
        'tags' => $temp['tags'],
        'page' => $temp['nextPage']
        ));
    }

    public function ajaxPageAction(Request $request,$page){
         $temp = $this->getTags($page,14);
        return $this->render('TopxiaWebBundle:Tag:index-tag-detail.html.twig', array(
        'tags' => $temp['tags'],
        'page' => $temp['nextPage']
        ));
    }

    private function getTags($page,$perPage){
        $perPageCount = $perPage;
        $total = $this->getTagService()->getAllTagCount();
        $currentPage = $page+1;
        $maxPage = ceil($total / $perPageCount) ? : 1;
        $start=0;
       
        if($currentPage>$maxPage){
            $currentPage = 1;
        }
       
        //保证最后一页也有$perPage条记录
        if($currentPage==$maxPage){
      
            if($total>$perPageCount){
                     $start = $total-$perPageCount;
            }else{
                 $start =0;
            }
           
            // $nextPage = 1;
        }else{
            
             $start = ($currentPage - 1) * $perPageCount;
             // $nextPage = $currentPage +1;
        }
     
        $tags = $this->getCustomTagService()->findAllTags($start, $perPageCount);
        
        return array("tags"=>$tags,"nextPage"=>$currentPage);
        // return $tags;
    }





    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
    private function getCustomTagService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.TagService');
    }


    private function getCustomCourseSearchService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
    }
}