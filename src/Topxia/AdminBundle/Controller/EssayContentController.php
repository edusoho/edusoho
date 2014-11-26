<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator; 

class EssayContentController extends BaseController
{
    public function indexAction(Request $request, $articleId ="1")
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        $essayContentItems = $this->getEssayContentService()->getEssayItems($articleId);

        return $this->render('TopxiaAdminBundle:EssayContent:index.html.twig', array(
            'category' => $category,
            'items' =>$essayContentItems,
            'articleId' => $articleId
        ));
    }

    public function createAction(Request $request, $id, $type)
    {
        if($type = 'unit'){

        }
    }

    public function chapterCreateAction(Request $request, $articleId)
    {
        $article = $this->getCategoryService()->getCategory($articleId);
        $category = $this->
        $type = $request->query->get('type');
        $parentId = $request->query->get('parentId');
        $type = in_array($type, array('chapter', 'unit')) ? $type : 'chapter';

        if($request->getMethod() == 'POST'){
            $chapter = $request->request->all();
            $chapter['articleId'] = $course['id'];
            $chapter = $this->getEssayContentService()->createChapter($chapter);
            return $this->render('TopxiaAdminBundle:EssayContent:list-item.html.twig', array(
                
                'article' => $article,
                'chapter' => $chapter,
            ));
        }

        return $this->render('TopxiaAdminBundle:EssayContent:chapter-modal.html.twig', array(
            'article' => $article,
            'type' => $type,
            'parentId' => $parentId
        ));
    }



    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getEssayContentService($value='')
    {
        return $this->getServiceKernel()->createService('EssayContent.EssayContentService');
    }

    private function getEssayService($value='')
    {
        return $this->getServiceKernel()->createService('Essay.EssayService');
    }

}