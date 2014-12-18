<?php
namespace Custom\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Question\Type\QuestionTypeFactory;

class TestpaperController extends BaseController
{
    public function indexAction(Request $request)
    {
        $category = $this->getCategoryService()->getCategory($request->query->get('categoryId'));

        $conditions = $request->query->all();
        if (!empty($conditions['knowledgeIds'])) {
            $conditions['knowledgeIds'] = explode(',', $conditions['knowledgeIds']);
            $knowledges = $this->getKnowledgeService()->findKnowledgeByIds($conditions['knowledgeIds']);
        } else {
            $knowledges = array();
        }

        if (!empty($conditions['tagIds'])) {
            $conditions['tagIds'] = explode(',', $conditions['tagIds']);
            $tags = $this->getTagService()->findTagsByIds($conditions['tagIds']);
        } else {
            $tags = array();
        }

        $conditions['target'] = "category-{$category['id']}";

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpapersCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' ,'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($testpapers, 'updatedUserId'));

        return $this->render('CustomAdminBundle:Testpaper:index.html.twig', array(
            'category' => $category,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'knowledges' => $knowledges,
            'tags' => $tags,
        ));
    }

    public function createAction(Request $request)
    {
        $category = $this->getCategoryService()->getCategory($request->query->get('categoryId'));

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['ranges'] = empty($fields['ranges']) ? array() : explode(',', $fields['ranges']);
            $fields['target'] = "category-{$category['id']}";
            $fields['pattern'] = 'QuestionType';
            list($testpaper, $items) = $this->getTestpaperService()->createTestpaper($fields);
            return $this->redirect($this->generateUrl('admin_testpaper_items',array('id' => $testpaper['id'])));
        }

        $typeNames = $this->get('topxia.twig.web_extension')->getDict('questionType');
        $types = array();
        foreach ($typeNames as $type => $name) {
            $typeObj = QuestionTypeFactory::create($type);
            $types[] = array(
                'key' => $type,
                'name' => $name,
                'hasMissScore' => $typeObj->hasMissScore(),
            );
        }

        return $this->render('CustomAdminBundle:Testpaper:create.html.twig', array(
            'category'    => $category,
            'types' => $types,
        ));
    }

    public function createAdvancedAction(Request $request)
    {
        $category = $this->getCategoryService()->getCategory($request->query->get('categoryId'));

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            $fields['target'] = "category-{$category['id']}";
            $fields['pattern'] = 'Part';
            $fields['metas'] = array('parts' => json_decode($fields['parts'], true));
            $fields['knowledgeIds'] = explode(',', $fields['knowledgeIds']);
            $fields['tagIds'] = explode(',', $fields['tagIds']);
            unset($fields['parts']);

            $testpaper= $this->getTestpaperService()->createTestpaperAdvanced($fields);
            return $this->redirect($this->generateUrl('admin_testpaper',array('categoryId' => $category['id'])));
        }

        $typeNames = $this->get('topxia.twig.web_extension')->getDict('questionType');
        $types = array();
        foreach ($typeNames as $type => $name) {
            $typeObj = QuestionTypeFactory::create($type);
            $types[] = array(
                'key' => $type,
                'name' => $name,
                'hasMissScore' => $typeObj->hasMissScore(),
            );
        }

        return $this->render('CustomAdminBundle:Testpaper:create-advanced.html.twig', array(
            'category'    => $category,
            'types' => $types,
        ));
    }

    public function buildCheckAction(Request $request)
    {
        $category = $this->getCategoryService()->getCategory($request->query->get('categoryId'));

        $data = $request->request->all();
        $data['target'] = "category-{$category['id']}";
        $data['ranges'] = empty($data['ranges']) ? array() : explode(',', $data['ranges']);
        $result = $this->getTestpaperService()->canBuildTestpaper('QuestionType', $data);
        return $this->createJsonResponse($result);
    }

    public function buildItemsAction(Request $request)
    {
        $part = json_decode($request->query->get('part'), true);
        $result = $this->getTestpaperService()->makeItemsByPart($part);
        return $this->createJsonResponse($result);
    }
    public function updateAction(Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        if (empty($testpaper)) {
            throw $this->createNotFoundException('试卷不存在');
        }

        $category = $this->getCategoryByTarget($testpaper['target']);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $testpaper = $this->getTestpaperService()->updateTestpaper($id, $data);
            $this->setFlashMessage('success', '试卷信息保存成功！');
            return $this->redirect($this->generateUrl('admin_testpaper', array('categoryId' => $category['id'])));
        }

        return $this->render('CustomAdminBundle:Testpaper:update.html.twig', array(
            'category'    => $category,
            'testpaper' => $testpaper,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getTestpaperService()->deleteTestpaper($id);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request)
    {
        $ids = $request->request->get('ids');

        foreach (is_array($ids) ? $ids : array() as $id) {
            $this->getTestpaperService()->deleteTestpaper($id);
        }

        return $this->createJsonResponse(true);
    }

    public function publishAction (Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->publishTestpaper($id);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        $category = $this->getCategoryByTarget($testpaper['target']);

        return $this->render('CustomAdminBundle:Testpaper:testpaper-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user' => $user,
            'category' => $category,
        ));
    }

    public function closeAction (Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->closeTestpaper($id);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        $category = $this->getCategoryByTarget($testpaper['target']);

        return $this->render('CustomAdminBundle:Testpaper:testpaper-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user' => $user,
            'category' => $category,
        ));
    }

    public function previewAction(Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }
        if($testpaper['pattern'] == 'QuestionType') {
            return $this->redirect($this->generateUrl('course_manage_preview_test', array('testId' => $id)));
        }
        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])){
            throw $this->createAccessDeniedException('无权预览试卷！');
        }

        $items = $this->getTestpaperService()->previewTestpaper($id);

        $total = $this->makeTestpaperTotal($testpaper, $items);

        return $this->render('CustomAdminBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items' => $items,
            'limitTime' => $testpaper['limitedTime'] * 60,
            'paper' => $testpaper,
            'id' => 0,
            'isPreview' => 'preview',
            'total' => $total
        ));
    }

    private function makeTestpaperTotal ($testpaper, $items)
    {
        $total = array();
        foreach ($items as $item) {

        }
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
                $total[$type]['missScore'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
                if (array_key_exists('missScore', $testpaper['metas']) and array_key_exists($type, $testpaper["metas"]["missScore"])){
                    $total[$type]['missScore'] =  $testpaper["metas"]["missScore"][$type];
                } else {
                    $total[$type]['missScore'] = 0;
                }
            }
        }

        return $total;
    }
    
    private function getTestpaperWithException($course, $testpaperId)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        if ($testpaper['target'] != "course-{$course['id']}") {
            throw $this->createAccessDeniedException();
        }
        return $testpaper;
    }

    private function getCategoryByTarget($target)
    {
        $id = intval(str_replace('category-', '', $target));
        return $this->getCategoryService()->getCategory($id);
    }

    public function itemsAction(Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        if(empty($testpaper)){
            throw $this->createNotFoundException('试卷不存在');
        }

        $category = $this->getCategoryByTarget($testpaper['target']);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            if (empty($data['questionId']) or empty($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目不能为空！');
            }
            if (count($data['questionId']) != count($data['scores'])) {
                return $this->createMessageResponse('error', '试卷题目数据不正确');
            }

            $data['questionId'] = array_values($data['questionId']);
            $data['scores'] = array_values($data['scores']);

            $items = array();
            foreach ($data['questionId'] as $index => $questionId) {
                $items[] = array('questionId' => $questionId, 'score' => $data['scores'][$index]);
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $items);

            $this->setFlashMessage('success', '试卷题目保存成功！');
            return $this->redirect($this->generateUrl('admin_testpaper',array( 'categoryId' => $category['id'])));
        }

        $items = $this->getTestpaperService()->getTestpaperItems($testpaper['id']);

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));

        $subItems = array();
        foreach ($items as $key => $item) {
            if ($item['parentId'] > 0) {
                $subItems[$item['parentId']][] = $item;
                unset($items[$key]);
            }
        }

        return $this->render('CustomAdminBundle:Testpaper:items.html.twig', array(
            'category' => $category,
            'testpaper' => $testpaper,
            'items' => ArrayToolkit::group($items, 'questionType'),
            'subItems' => $subItems,
            'questions' => $questions,
        ));
    }

    public function itemsResetAction(Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        if(empty($testpaper)){
            throw $this->createNotFoundException('试卷不存在');
        }

        $category = $this->getCategoryByTarget($testpaper['target']);

        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data['target'] = "category-{$category['id']}";
            $this->getTestpaperService()->buildTestpaper($testpaper['id'], $data);
            return $this->redirect($this->generateUrl('admin_testpaper_items', array('id' => $id)));
        }

        $typeNames = $this->get('topxia.twig.web_extension')->getDict('questionType');
        $types = array();
        foreach ($typeNames as $type => $name) {
            $typeObj = QuestionTypeFactory::create($type);
            $types[] = array(
                'key' => $type,
                'name' => $name,
                'hasMissScore' => $typeObj->hasMissScore(),
            );
        }


        return $this->render('CustomAdminBundle:Testpaper:items-reset.html.twig', array(
            'category'    => $category,
            'testpaper' => $testpaper,
            'types' => $types,
        ));
    }

    public function itemPickerAction(Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        $category = $this->getCategoryByTarget($testpaper['target']);

        $conditions = $request->query->all();

        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "category-{$category['id']}";
        }

        $conditions['parentId'] = 0;
        $conditions['excludeIds'] = empty($conditions['excludeIds']) ? array() : explode(',', $conditions['excludeIds']);

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }


        $replace = empty($conditions['replace']) ? '' : $conditions['replace'];

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchQuestionsCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->searchQuestions(
                $conditions,
                array('createdTime' ,'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        return $this->render('CustomAdminBundle:Testpaper:item-picker-modal.html.twig', array(
            'category' => $category,
            'testpaper' => $testpaper,
            'questions' => $questions,
            'replace' => $replace,
            'paginator' => $paginator,
            'conditions' => $conditions,
        ));

    }

    public function itemPickedAction(Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        $category = $this->getCategoryByTarget($testpaper['target']);

        $question = $this->getQuestionService()->getQuestion($request->query->get('questionId'));
        if (empty($question)) {
            throw $this->createNotFoundException();
        }

        if ($question['subCount'] > 0) {
            $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id']);
        } else {
            $subQuestions = array();
        }

        return $this->render('CustomAdminBundle:Testpaper:item-picked.html.twig', array(
            'category'    => $category,
            'testpaper' => $testpaper,
            'question' => $question,
            'subQuestions' => $subQuestions,
            'type' => $question['type']
        ));

    }





    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Tag.TagService');
    }

}
