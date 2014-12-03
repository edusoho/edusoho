<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator; 

class EssayContentController extends BaseController
{
    public function indexAction(Request $request, $essayId)
    {
        $essay = $this->getEssayService()->getEssay($essayId);
        $category = $this->getCategoryService()->getCategory($essay['categoryId']);
        $essayContentItems = $this->getEssayContentService()->getEssayItems($essayId);

        return $this->render('TopxiaAdminBundle:EssayContent:index.html.twig', array(
            'category' => $category,
            'items' =>$essayContentItems,
            'essay' => $essay
        ));
    }

    public function listAction(Request $request, $essayId)
    {
        $essay = $this->getEssayService()->getEssay($essayId);
        $category = $this->getCategoryService()->getCategory($essay['categoryId']);
        $parentId = $request->query->get('parentId');
        $knowledgeIds = $request->query->get('knowledgeIds');
        $knowledgeIds = empty($knowledgeIds) ? array() : explode(',',$knowledgeIds);
        $tagIds = $request->query->get('tagIds');
        $tagIds = empty($tagIds) ? array() : explode(',',$tagIds);
        $title = $request->query->get('keywords');

        $conditions = array(
            'tagIds' => $tagIds,
            'knowledgeIds' => $knowledgeIds,
            'categoryId' => $essay['categoryId'],
            'title' => $title,
        );

        $articleMaterialsCount = $this->getArticleMaterialService()->searchArticleMaterialsCount($conditions);

        $paginator = new Paginator($this->get('request'), $articleMaterialsCount, 8);

        $articleMaterials = $this->getArticleMaterialService()->searchArticleMaterials(
            $conditions, 
            array('createdTime','desc'),
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );

        $knowledges = $this->getKnowledgeService()->findKnowledgeByIds(ArrayToolkit::column($articleMaterials,'mainKnowledgeId'));
        $knowledges = ArrayToolkit::index($knowledges, 'id');

        $knowledgeSearchs = !empty($knowledgeIds) ? $this->getKnowledgeService()->findKnowledgeByIds($knowledgeIds):array();
        $tagSearchs = !empty($tagIds) ? $this->getTagService()->findTagsByIds($tagIds):array();

        return $this->render('TopxiaAdminBundle:EssayContent:content-modal.html.twig',array(
            'category' => $category,
            'parentId' => $parentId,
            'essay' => $essay,
            'articleMaterials' => $articleMaterials,
            'paginator' => $paginator,
            'knowledges' => $knowledges,
            'tagSearchs' => $tagSearchs,
            'knowledgeSearchs' => $knowledgeSearchs,
            'articleMaterialsCount' => $articleMaterialsCount,
        ));
    }

    public function createAction(Request $request, $essayId)
    {
        $materialIds = $request->request->get('materialIds');
        $chapterId = $request->request->get('chapterId');
        $chapterId = empty($chapterId)? '0' :substr($chapterId,'8');

        foreach ($materialIds as $materialId) {
            $fields = array(
                'articleId' => $essayId,
                'chapterId' => $chapterId,
                'materialId' => $materialId,
            );

            $this->getEssayContentService()->createContent($fields);
        }

        return $this->createJsonResponse(true);
    }

    public function chapterCreateAction(Request $request, $essayId)
    {
        $essay = $this->getEssayService()->getEssay($essayId);
        $category = $this->getCategoryService()->getCategory($essay['categoryId']);
        $type = $request->query->get('type');
        $parentId = $request->query->get('parentId');
        $type = in_array($type, array('chapter', 'unit')) ? $type : 'chapter';

        if($request->getMethod() == 'POST'){
            $chapter = $request->request->all();
            $chapter['articleId'] = $essayId;
            $chapter = $this->getEssayContentService()->createChapter($chapter);
            return $this->render('TopxiaAdminBundle:EssayContent:list-chapter-tr.html.twig', array(
                'category' => $category,
                'essay' => $essay,
                'chapter' => $chapter,
            ));
        }

        return $this->render('TopxiaAdminBundle:EssayContent:chapter-modal.html.twig', array(
            'category' => $category,
            'essay' => $essay,
            'type' => $type,
            'parentId' => $parentId
        ));
    }

    public function editAction(Request $request, $contentId, $essayId)
    {
        if($request->getMethod() == 'POST'){
            $materialId = $request->request->all();
            $this->getEssayContentService()->updateContent($contentId, $materialId);
            return $this->createJsonResponse(true);
        }

        $essay = $this->getEssayService()->getEssay($essayId);
        $category = $this->getCategoryService()->getCategory($essay['categoryId']);
        $knowledgeIds = $request->query->get('knowledgeIds');
        $knowledgeIds = empty($knowledgeIds) ? array() : explode(',',$knowledgeIds);
        $tagIds = $request->query->get('tagIds');
        $tagIds = empty($tagIds) ? array() : explode(',',$tagIds);
        $title = $request->query->get('keywords');

        $conditions = array(
            'tagIds' => $tagIds,
            'knowledgeIds' => $knowledgeIds,
            'categoryId' => $essay['categoryId'],
            'title' => $title,
        );

        $articleMaterialsCount = $this->getArticleMaterialService()->searchArticleMaterialsCount($conditions);

        $paginator = new Paginator($this->get('request'), $articleMaterialsCount, 8);

        $articleMaterials = $this->getArticleMaterialService()->searchArticleMaterials(
            $conditions, 
            array('createdTime','desc'),
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );

        $knowledges = $this->getKnowledgeService()->findKnowledgeByIds(ArrayToolkit::column($articleMaterials,'mainKnowledgeId'));
        $knowledges = ArrayToolkit::index($knowledges, 'id');

        $knowledgeSearchs = !empty($knowledgeIds) ? $this->getKnowledgeService()->findKnowledgeByIds($knowledgeIds):array();
        $tagSearchs = !empty($tagIds) ? $this->getTagService()->findTagsByIds($tagIds):array();

        return $this->render('TopxiaAdminBundle:EssayContent:content-edit-modal.html.twig',array(
            'category' => $category,
            'essay' => $essay,
            'articleMaterials' => $articleMaterials,
            'paginator' => $paginator,
            'knowledges' => $knowledges,
            'articleMaterialsCount' => $articleMaterialsCount,
            'tagSearchs' => $tagSearchs,
            'knowledgeSearchs' => $knowledgeSearchs,
            'contentId' => $contentId
        ));
    }

    public function chapterEditAction(Request $request, $essayId, $chapterId)
    {
        $essay = $this->getEssayService()->getEssay($essayId);
        $category = $this->getCategoryService()->getCategory($essay['categoryId']);
        $chapter = $this->getEssayContentService()->getChapter($essayId, $chapterId);
        if (empty($chapter)) {
            throw $this->createNotFoundException("章节(#{$chapterId})不存在！");
        }

        if($request->getMethod() == 'POST'){
            $fields = $request->request->all();
            $fields['essayId'] = $essayId;
            $chapter = $this->getEssayContentService()->updateChapter($essayId, $chapterId, $fields);
            return $this->render('TopxiaAdminBundle:EssayContent:list-chapter-tr.html.twig', array(
                'category' => $category,
                'essay' => $essay,
                'chapter' => $chapter,
            ));
        }

        return $this->render('TopxiaAdminBundle:EssayContent:chapter-modal.html.twig', array(
            'category' => $category,
            'essay' => $essay,
            'chapter' => $chapter,
            'type' => $chapter['type'],
        )); 
    }

    public function chapterDeleteAction(Request $request, $essayId, $chapterId)
    {
        $this->getEssayContentService()->deleteChapter($essayId, $chapterId);
        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $essayId, $contentId)
    {
        $this->getEssayContentService()->deleteContent($essayId, $contentId);
        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request, $essayId)
    {
        $ids = $request->request->get('ids');
        if(!empty($ids)){
            $this->getEssayContentService()->sortEssayItems($essayId, $request->request->get('ids'));
        }
        return $this->createJsonResponse(true);
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getEssayContentService()
    {
        return $this->getServiceKernel()->createService('EssayContent.EssayContentService');
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

    private function getArticleMaterialService()
    {
        return $this->getServiceKernel()->createService('ArticleMaterial.ArticleMaterialService');
    }

    private function getEssayService()
    {
        return $this->getServiceKernel()->createService('Essay.EssayService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Tag.TagService');
    }
}