<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;


class TagController extends BaseController
{
     public function indexAction (Request $request, $tagId)
    {
        //获取前18个标签
        $tags = $this->getTagService()->findAllTags(0, 18);
      if (empty($tags)) {
            throw $this->createNotFoundException();
        }

        if(empty($tagId)){
            $tagId=$tags[0]['id'];
        }
         $tagDetail = $this->getTagService()->getTag($tagId);

        if (empty($tagDetail)) {
            throw $this->createNotFoundException();
        }


        $conditions = array('tagId' => $tagId);
         $count = $this->getCustomCourseSearchService()->searchCourseCount($conditions);
           
        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses = $this->getCustomCourseSearchService()->searchCourses($conditions,null, $paginator->getOffsetCount(),  $paginator->getPerPageCount());
      
        return $this->render('TopxiaWebBundle:Tag:tag-index.html.twig', array(
            'tags' => $tags,
            'tagDetail' => $tagDetail,
            'courses' => $courses 
        ));

    }
    public function nextAction(Request $request){
        $perPageCount = 14;
        $total = $this->getTagService()->getAllTagCount();
        $currentPage = $request->query->get('page');
        $maxPage = ceil($total / $perPage) ? : 1;
        $start=0;
        //保证最后一页也有14条记录
        if($currentPage==$maxPage){
            $start = $total-14;
            $nextPage = 1;
        }else{
             $start = ($currentPage - 1) * 14;
             $nextPage = $currentPage +1;
        }
        $tags = $this->getTagService()->findAllTags($start, 14);
        return $this->render('CustomWebBundle:Default:index.html.twig', array(
        'tags' => $tags,
        'page' => $nextPage
        ));
    }




    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getCustomCourseSearchService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseSearchService');
    }
}