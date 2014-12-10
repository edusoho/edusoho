<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\DefaultController as TopXiaDefaultController;
use Symfony\Component\HttpFoundation\Request;


class DefaultTagController extends TopXiaDefaultController
{
     public function indexAction ()
    {
        
        //获取前14个标签
        $tags = $this->getTagService()->findAllTags(0, 14);
        $length=count($tags);
        for(int i = $length;i++;i<14){
            $tags[i] = $this->crateNewTag();
        }
        
        return $this->render('TopxiaWebBundle:Tag:default-tag.html.twig', array(
            'tags' => $tags
        ));
    }

    public function crateNewTag(){
    return array('id' => 0, 'name' => '', 'description' => '')
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
}