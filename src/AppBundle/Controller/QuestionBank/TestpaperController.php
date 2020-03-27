<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\Service\CategoryService;
use Biz\Question\Service\QuestionService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use ExamParser\Writer\WriteDocx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TestpaperController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $conditions = array(
            'bank_id' => $questionBank['id'],
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAssessmentService()->countAssessments($conditions),
            10
        );

        $testpapers = $this->getAssessmentService()->searchAssessments(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updated_user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($testpapers, 'id'));

        return $this->render('question-bank/testpaper/index.html.twig', array(
            'questionBank' => $questionBank,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'testpaperActivities' => $testpaperActivities,
        ));
    }

    public function getTestpaperHtmlAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getItemBankService()->getItemBank($id);

        $conditions = array(
            'bank_id' => $questionBank['id'],
            'nameLike' => $request->query->get('keyword', ''),
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAssessmentService()->countAssessments($conditions),
            10
        );

        $testpapers = $this->getAssessmentService()->searchAssessments(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updated_user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($testpapers, 'id'));

        return $this->render('question-bank/testpaper/testpaper-list-tr.html.twig', array(
            'questionBank' => $questionBank,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'testpaperActivities' => $testpaperActivities,
            'isSearch' => true,
        ));
    }

    public function importAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        return $this->forward('AppBundle:Question/QuestionParser:read', array(
            'request' => $request,
            'type' => 'testpaper',
            'questionBank' => $questionBank,
        ));
    }

    public function createAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        if ('POST' === $request->getMethod()) {
            $assessment = $request->request->get('baseInfo', array());
            $sections = $request->request->get('sections', array());
            $assessment['bank_id'] = $id;

            if (empty($sections)) {
                return $this->createMessageResponse('error', '试卷模块不能为空！');
            }
            $assessment['sections'] = json_decode($sections, true);

            if ($this->calculateItemCount($assessment['sections']) > 2000) {
                return $this->createMessageResponse('error', '试卷题目数量不能超过2000！');
            }

            $this->getAssessmentService()->createAssessment($assessment);

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('question_bank_manage_testpaper_list', array('id' => $id)),
            ));
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $types = $this->getQuestionTypes();

        return $this->render('question-bank/testpaper/manage/testpaper-form.html.twig', array(
            'types' => $types,
            'questionBank' => $questionBank,
            'showBaseInfo' => '1',
        ));
    }

    public function createRandomTestpaperAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();

            $fields['bankId'] = $id;
            $fields['pattern'] = 'questionType';

            $testpaper = $this->getTestpaperService()->buildTestpaper($fields, 'random_testpaper');

            return $this->redirect(
                $this->generateUrl(
                    'question_bank_manage_testpaper_edit',
                    array('id' => $id, 'testpaperId' => $testpaper['id'], 'showBaseInfo' => '0')
                )
            );
        }

        $types = $this->getQuestionTypes();

        $conditions = array(
            'types' => array_keys($types),
            'bankId' => $id,
            'parentId' => 0,
        );

        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $questionNums = ArrayToolkit::index($questionNums, 'type');
        $categoryTree = $this->getCategoryService()->getCategoryTree($questionBank['id']);

        return $this->render('question-bank/testpaper/random/testpaper-form.html.twig', array(
            'categoryTree' => $categoryTree,
            'types' => $types,
            'questionNums' => $questionNums,
            'questionBank' => $questionBank,
        ));
    }

    public function editAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $showBaseInfo = $request->query->get('showBaseInfo', '1');
        $questionBank = $this->getItemBankService()->getItemBank($id);
        $assessment = $this->getAssessmentService()->getAssessment($testpaperId);

        if (!$assessment || $assessment['bank_id'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('draft' != $assessment['status']) {
            return $this->createMessageResponse('error', '已发布或已关闭的试卷不能再修改题目');
        }

        if ('POST' === $request->getMethod()) {
            $assessment = $request->request->get('baseInfo', array());
            $sections = $request->request->get('sections', array());

            if (empty($sections)) {
                return $this->createMessageResponse('error', '试卷模块不能为空！');
            }
            $assessment['sections'] = json_decode($sections, true);

            if ($this->calculateItemCount($assessment['sections']) > 2000) {
                return $this->createMessageResponse('error', '试卷题目数量不能超过2000！');
            }

            $this->getAssessmentService()->updateAssessment($testpaperId, $assessment);

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('question_bank_manage_testpaper_list', array('id' => $id)),
            ));
        }

        $assessmentDetail = $this->getAssessmentService()->showAssessment($assessment['id']);
        $sections = $this->setSectionsType($assessmentDetail['sections']);
        $itemCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['id']);
        $itemCategories = ArrayToolkit::index($itemCategories, 'id');

        return $this->render('question-bank/testpaper/manage/testpaper-form.html.twig', array(
            'questionBank' => $questionBank,
            'testpaper' => $assessment,
            'sections' => $sections,
            'itemCategories' => $itemCategories,
            'showBaseInfo' => $showBaseInfo,
        ));
    }

    public function deleteTestpapersAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $ids = $request->request->get('ids');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);
        if (empty($testpapers)) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getTestpaperService()->deleteTestpapers($ids);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($testpaperId);

        if (empty($assessment) || $assessment['bank_id'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $this->getAssessmentService()->deleteAssessment($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($testpaperId);
        if (empty($assessment) || $assessment['bank_id'] != $id) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getAssessmentService()->openAssessment($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function closeAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($testpaperId);
        if (empty($assessment) || $assessment['bank_id'] != $id) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getAssessmentService()->closeAssessment($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper || $testpaper['bankId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('closed' === $testpaper['status']) {
            return $this->createMessageResponse('warning', 'testpaper already closed');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('testpaper/manage/preview.html.twig', array(
            'questions' => $questions,
            'limitedTime' => $testpaper['limitedTime'],
            'paper' => $testpaper,
            'paperResult' => array(),
            'total' => $total,
            'attachments' => $attachments,
            'questionTypes' => $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper),
        ));
    }

    public function exportAction(Request $request, $id, $testpaperId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['bankId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $questions = $this->getTestpaperService()->buildExportTestpaperItems($testpaperId);

        $fileName = $testpaper['name'].'.docx';
        $baseDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $path = $baseDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new WriteDocx($path);
        $writer->write($questions);

        $headers = array(
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        );

        return new BinaryFileResponse($path, 200, $headers);
    }

    public function jsonAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createJsonResponse(array());
        }

        $conditions = array(
            'bankId' => $id,
            'type' => 'testpaper',
            'keyword' => $request->query->get('keyword', ''),
        );
        $totalCount = $this->getTestpaperService()->searchTestpaperCount($conditions);
        $conditions['status'] = 'open';
        $openCount = $this->getTestpaperService()->searchTestpaperCount($conditions);

        $pagination = new Paginator(
            $request,
            $openCount,
            10
        );

        $testPapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $pagination->getOffsetCount(),
            $pagination->getPerPageCount()
        );

        foreach ($testPapers as &$testPaper) {
            $testPaper = ArrayToolkit::parts($testPaper, array(
                'id',
                'name',
                'score',
            ));
        }

        $data = array(
            'testPapers' => $testPapers,
            'totalCount' => $totalCount,
            'openCount' => $openCount,
        );

        return $this->createJsonResponse($data);
    }

    public function questionPickAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $itemBank = $this->getItemBankService()->getItemBank($id);
        $conditions = $request->query->all();

        $conditions['bank_id'] = $id;
        if (!empty($conditions['exclude_ids'])) {
            $conditions['exclude_ids'] = explode(',', $conditions['exclude_ids']);
        }

        $orderBy = array('created_time' => 'DESC');

        $paginator = new Paginator(
            $this->get('request'),
            $this->getItemService()->countItems($conditions),
            10
        );

        $items = $this->getItemService()->searchItems(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getItemCategoryService()->getItemCategoryTree($itemBank['id']);
        $itemCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($itemBank['id']);
        $itemCategories = ArrayToolkit::index($itemCategories, 'id');

        return $this->render('question-bank/widgets/question-pick-modal.html.twig', array(
            'isSelectBank' => $request->request->get('isSelectBank', 0),
            'items' => $items,
            'paginator' => $paginator,
            'questionBank' => $itemBank,
            'categoryTree' => $categories,
            'itemCategories' => $itemCategories,
            'excludeIds' => empty($conditions['excludeIds']) ? '' : $conditions['excludeIds'],
        ));
    }

    public function questionSearchAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $questionBank = $this->getItemBankService()->getItemBank($id);
        $conditions = $request->query->all();

        $conditions['bank_id'] = $id;
        $orderBy = array('created_time' => 'DESC');
        $paginator = new Paginator(
            $this->get('request'),
            $this->getItemService()->countItems($conditions),
            10
        );

        if (!empty($conditions['categoryId'])) {
            $childrenIds = $this->getItemCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $childrenIds[] = $conditions['categoryId'];
            $conditions['category_ids'] = $childrenIds;
            unset($conditions['categoryId']);
        }

        $items = $this->getItemService()->searchItems(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $itemCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['id']);
        $itemCategories = ArrayToolkit::index($itemCategories, 'id');

        return $this->render('question-bank/widgets/question-pick-body.html.twig', array(
            'items' => $items,
            'paginator' => $paginator,
            'questionBank' => $questionBank,
            'itemCategories' => $itemCategories,
        ));
    }

    public function pickedQuestionAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $typeQuestions = $request->request->get('typeQuestions', array());
        $itemBank = $this->getItemBankService()->getItemBank($id);
        $itemCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($itemBank['id']);
        $itemCategories = ArrayToolkit::index($itemCategories, 'id');
        $typeHtml = array();
        foreach ($typeQuestions as $type => $items) {
            if (empty($items)) {
                continue;
            }

            $itemIds = array_keys($items);
            $items = $this->getItemService()->findItemsByIds($itemIds, true);

            $typeHtml[$type] = $this->renderView('question-bank/widgets/picked-question.html.twig', array(
                'items' => $items,
                'questionBank' => $itemBank,
                'itemCategories' => $itemCategories,
                'type' => $type,
            ));
        }

        return $this->createJsonResponse($typeHtml);
    }

    public function buildCheckAction(Request $request, $id, $type)
    {
        $bank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($bank)) {
            throw $this->createAccessDeniedException();
        }

        $data = $request->request->all();
        $data['bankId'] = $id;

        $result = $this->getTestpaperService()->canBuildTestpaper($type, $data);

        return $this->createJsonResponse($result);
    }

    protected function calculateItemCount($sections)
    {
        $itemCount = 0;
        foreach ($sections as $section) {
            if (!empty($section['items'])) {
                $itemCount += count($section['items']);
            }
        }

        return $itemCount;
    }

    protected function setSectionsType($sections)
    {
        foreach ($sections as &$section) {
            $section['type'] = reset($section['items'])['type'];
        }

        return ArrayToolkit::index($sections, 'type');
    }

    protected function getQuestionTypes()
    {
        $typesConfig = $this->get('extension.manager')->getQuestionTypes();

        $types = array();
        foreach ($typesConfig as $type => $typeConfig) {
            $types[$type] = array(
                'name' => $typeConfig['name'],
                'hasMissScore' => $typeConfig['hasMissScore'],
                'seqNum' => $typeConfig['seqNum'],
            );
        }

        return $types;
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->createService('ItemBank:ItemBank:ItemBankService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return ItemCategoryService
     */
    protected function getItemCategoryService()
    {
        return $this->createService('ItemBank:Item:ItemCategoryService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }
}
