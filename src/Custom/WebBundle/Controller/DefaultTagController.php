<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\DefaultController as TopXiaDefaultController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;


class DefaultTagController extends TopXiaDefaultController
{
     public function indexAction ()
    {
        
        //获取前14个标签
        $tags = $this->getTagService()->findAllTags(0, 14);
        $length=count($tags);
        for($i = $length;$i<14;$i++){
            $tags[$i] = $this->crateNewTag();
        }
        return $this->render('TopxiaWebBundle:Tag:default-tag.html.twig', array(
            'tags' => $tags,
            'page' => 1
        ));
    }

    public function crateNewTag(){
        return array('id' => 0, 'name' => '', 'description' => '');
    }

    

    public function pageAction(Request $request,$page){
        $perPageCount = 14;
        $total = $this->getTagService()->getAllTagCount();
        $currentPage = $page;
        $maxPage = ceil($total / $perPageCount) ? : 1;
        $start=0;
        //保证最后一页也有14条记录
        if($currentPage==$maxPage){
            if($total>$perPageCount){
                 $start = $total-$perPageCount;
            }else{
                 $start=0;
            }
            
            $nextPage = 1;
        }else{
             $start = ($currentPage - 1) * $perPageCount;
             $nextPage = $currentPage +1;
        }
        $tags = $this->getTagService()->findAllTags($start, $perPageCount);
        return $this->render('TopxiaWebBundle:Tag:default-tag.html.twig', array(
        'tags' => $tags,
        'page' => $nextPage
        ));
    }




    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}