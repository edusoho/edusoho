<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator; 

class EssayContentController extends BaseController
{
    public function indexAction(Request $request, $articleId)
    {
        $categoryId='3';
        $category = $this->getCategoryService()->getCategory($categoryId);
        $essayContentItems = $this->getEssayContentService()->getEssayItems($articleId);

        return $this->render('TopxiaAdminBundle:EssayContent:index.html.twig', array(
            'category' => $category,
            'items' =>$essayContentItems,
            'articleId' => $articleId
        ));
    }

    public function createAction(Request $request, $articleId)
    {
        $categoryId = '3';
        $parentId = $request->query->get('parentId');
        $method = $request->query->get('method');
        if (empty($method)){
            $conditions = array('categoryId' => $categoryId);
        } elseif($method == 'tag'){
            $conditions = array(
                'tagIds' => $tagIds,
                'knowledgeId' => $knowledgeId,
                'categoryId' => $categoryId
            );
        } else {
            $conditions = array(
                'title' => $title,
                'knowledgeId' => $knowledgeId,
                'categoryId' => $categoryId
            );
        }

        $articleMaterialsCount = $this->getArticleMaterialService()->searchArticleMaterialsCount($conditions);

        $paginator = new Paginator($this->get('request'), $articleMaterialsCount, 8);

        $articleMaterials = $this->getArticleMaterialService()->searchArticleMaterials(
            $conditions, 
            array('createdTime','desc'),
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );

        $category = $this->getCategoryService()->getCategory($categoryId);

        $knowledges = $this->getKnowledgeService()->findKnowledgeByIds(ArrayToolkit::column($articleMaterials,'mainKnowledgeId'));
        $knowledges = ArrayToolkit::index($knowledges, 'id');
        
        return $this->render('TopxiaAdminBundle:EssayContent:content-modal.html.twig',array(
            'category' => $category,
            'articleMaterials' => $articleMaterials,
            'paginator' => $paginator,
            'knowledges' => $knowledges,
            'articleMaterialsCount' => $articleMaterialsCount,
            'method' => $method
        ));
    }

    public function chapterCreateAction(Request $request, $articleId)
    {

        $categoryId='3';
        $category = $this->getCategoryService()->getCategory($categoryId);
        $type = $request->query->get('type');
        $parentId = $request->query->get('parentId');
        $type = in_array($type, array('chapter', 'unit')) ? $type : 'chapter';

        if($request->getMethod() == 'POST'){
            $chapter = $request->request->all();
            $chapter['articleId'] = $articleId;
            $chapter = $this->getEssayContentService()->createChapter($chapter);
            return $this->render('TopxiaAdminBundle:EssayContent:list-chapter-tr.html.twig', array(
                'category' => $category,
                'articleId' => $articleId,
                'chapter' => $chapter,
            ));
        }

        return $this->render('TopxiaAdminBundle:EssayContent:chapter-modal.html.twig', array(
            'category' => $category,
            'articleId' => $articleId,
            'type' => $type,
            'parentId' => $parentId
        ));
    }

    public function chapterEditAction(Request $request, $articleId, $chapterId)
    {
        $categoryId='3';
        $category = $this->getCategoryService()->getCategory($categoryId);
        $chapter = $this->getEssayContentService()->getChapter($articleId, $chapterId);
        if (empty($chapter)) {
            throw $this->createNotFoundException("章节(#{$chapterId})不存在！");
        }

        if($request->getMethod() == 'POST'){
            $fields = $request->request->all();
            $fields['articleId'] = $articleId;
            $chapter = $this->getEssayContentService()->updateChapter($articleId, $chapterId, $fields);
            return $this->render('TopxiaAdminBundle:EssayContent:list-chapter-tr.html.twig', array(
                'category' => $category,
                'articleId' => $articleId,
                'chapter' => $chapter,
            ));
        }

        return $this->render('TopxiaAdminBundle:EssayContent:chapter-modal.html.twig', array(
            'category' => $category,
            'articleId' => $articleId,
            'chapter' => $chapter,
            'type' => $chapter['type'],
        )); 
    }

    public function chapterDeleteAction(Request $request, $articleId, $chapterId)
    {
        $this->getEssayContentService()->deleteChapter($articleId, $chapterId);
        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request, $articleId)
    {
        $ids = $request->request->get('ids');
        if(!empty($ids)){
            $this->getEssayContentService()->sortEssayItems($articleId, $request->request->get('ids'));
        }
        return $this->createJsonResponse(true);
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

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

    private function getArticleMaterialService()
    {
        return $this->getServiceKernel()->createService('ArticleMaterial.ArticleMaterialService');
    }

}