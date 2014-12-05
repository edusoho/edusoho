<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class LectureNoteController extends BaseController
{
    public function indexAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $category = $this->getCategoryService()->getCategory($course['subjectIds'][0]);

        $conditions = $request->query->all();
        if (!empty($conditions['knowledgeIds'])) {
            $conditions['knowledgeIds'] = explode(',', $conditions['knowledgeIds']);
            $knowledgeSearchs = $this->getKnowledgeService()->findKnowledgeByIds($conditions['knowledgeIds']);
        } else {
            $knowledgeSearchs = array();
        }

        if (!empty($conditions['tagIds'])) {
            $conditions['tagIds'] = explode(',', $conditions['tagIds']);
            $tagSearchs = $this->getTagService()->findTagsByIds($conditions['tagIds']);
        } else {
            $tagSearchs = array();
        }
        $conditions['categoryId'] = $category['id'];

        $articleMaterialsCount = $this->getArticleMaterialService()->searchArticleMaterialsCount($conditions);

        $paginator = new Paginator($this->get('request'), $articleMaterialsCount, 8);

        $articleMaterials = $this->getArticleMaterialService()->searchArticleMaterials(
            $conditions, 
            array('createdTime','desc'),
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );

        $knowledgeSearchs = !empty($knowledgeIds) ? $this->getKnowledgeService()->findKnowledgeByIds($knowledgeIds):array();
        $tagSearchs = !empty($tagIds) ? $this->getTagService()->findTagsByIds($tagIds):array();

        return $this->render('CustomWebBundle:LectureNote:modal.html.twig',array(
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

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
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