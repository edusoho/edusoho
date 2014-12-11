<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class LectureNoteController extends BaseController
{
    public function indexAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $conditions = $request->query->all();
        $lessonId = $conditions['lessonId'];
        $type = empty($conditions['type'])? 'lectureNote':$conditions['type'];
        $style = empty($conditions['style'])? 'essayMaterial':$conditions['style'];
// var_dump($conditions);exit();
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $category = $this->getCategoryService()->getCategory($course['subjectIds'][0]);

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

        $essayMaterialConditions = $style == 'essayMaterial' ? $conditions: array($category['id']) ;

        $essayMaterialsCount = $this->getArticleMaterialService()->searchArticleMaterialsCount($essayMaterialConditions);

        $paginator = new Paginator($this->get('request'), $essayMaterialsCount, 10);

        $essayMaterials = $this->getArticleMaterialService()->searchArticleMaterials(
            $essayMaterialConditions, 
            array('createdTime','desc'),
            $paginator->getOffsetCount(),  
            $paginator->getPerPageCount()
        );

        $essayConditions = $style != 'essayMaterial' ? $conditions: array($category['id']) ;

        $essayPaginator = new Paginator(
            $this->get('request'),
            $this->getEssayService()->searchEssaysCount($essayConditions),
            10
        );

        $essays = $this->getEssayService()->searchEssays(
            $essayConditions,
            array('createdTime', 'DESC'),
            $essayPaginator->getOffsetCount(),
            $essayPaginator->getPerPageCount()
        );

        $lectureNotes = $this->getLectureNoteService()->findLectureNotesByType($type);
        $knowledges = $this->getKnowledgeService()->findKnowledgeByIds(ArrayToolkit::column($essayMaterials,'mainKnowledgeId'));
        $knowledges = ArrayToolkit::index($knowledges, 'id');

        return $this->render('CustomWebBundle:LectureNote:modal.html.twig',array(
            'courseId' => $courseId,
            'lessonId' => $lessonId,
            'category' => $category,
            'essayMaterials' => $essayMaterials,
            'lectureNotes' => $lectureNotes,
            'style' => $style,
            'type' => $type,
            'essays' => $essays ,
            'paginator' => $paginator,
            'essayPaginator' => $essayPaginator,
            'knowledges' => $knowledges,
            'tagSearchs' => $tagSearchs,
            'knowledgeSearchs' => $knowledgeSearchs,
            'essayMaterialsCount' => $essayMaterialsCount,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        $conditions = $request->query->all();
        $type = empty($conditions['type'])? 'lectureNote':$conditions['type'];
        if ($conditions['style']== 'essay'){
            $essay = $this->getEssayService()->getEssay($request->request->get('id'));
            $fields = array(
                'courseId' => $courseId,
                'lessonId' => $conditions['lessonId'],
                'title' => $essay['title'],
                'essayId' => $essay['id'],
                'essayMaterialId' => '0',
                'type' => $type,
            );
        } else {
            $essayMaterial = $this->getArticleMaterialService()->getArticleMaterial($request->request->get('id'));
            $fields = array(
                'courseId' => $courseId,
                'lessonId' => $conditions['lessonId'],
                'title' => $essayMaterial['title'],
                'essayId' => '0',
                'essayMaterialId' => $essayMaterial['id'],
                'type' => $type,
            );
        }

        $lectureNote = $this->getLectureNoteService()->createLectureNote($fields);

        return $this->render('CustomWebBundle:LectureNote:list-item.html.twig',array(
            'lectureNote'=> $lectureNote,
        ));
    }

    public function deleteAction($id)
    {
        $lectureNote = $this->getLectureNoteService()->getLectureNote($id);
        if (empty($lectureNote)){
            return $this->createJsonResponse(array('status' => 'error', 'message'=>'内容不存在，无法删除'));
        }
        $course = $this->getCourseService()->tryManageCourse($lectureNote['courseId']);
        $this->getLectureNoteService()->deleteLectureNote($id);
        return $this->createJsonResponse(array('status' => 'true', 'message'=>'删除成功'));
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

    private function getLectureNoteService()
    {
        return $this->getServiceKernel()->createService('Custom:LectureNote.LectureNoteService');
    }
}