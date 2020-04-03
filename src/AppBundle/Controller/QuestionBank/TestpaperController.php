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

        $questionBank = $this->getItemBankService()->getItemBank($id);

        $conditions = array(
            'bank_id' => $questionBank['id'],
            'displayable' => 1,
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAssessmentService()->countAssessments($conditions),
            10
        );

        $assessments = $this->getAssessmentService()->searchAssessments(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($assessments, 'updated_user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($assessments, 'id'));

        return $this->render('question-bank/testpaper/index.html.twig', array(
            'questionBank' => $questionBank,
            'testpapers' => $assessments,
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
            'displayable' => 1,
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getAssessmentService()->countAssessments($conditions),
            10
        );

        $assessments = $this->getAssessmentService()->searchAssessments(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($assessments, 'updated_user_id');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($assessments, 'id'));

        return $this->render('question-bank/testpaper/testpaper-list-tr.html.twig', array(
            'questionBank' => $questionBank,
            'testpapers' => $assessments,
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

        $questionBank = $this->getItemBankService()->getItemBank($id);

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
            $assessment['displayable'] = 1;

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

        $questionBank = $this->getItemBankService()->getItemBank($id);

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();

            list($range, $sections) = $this->getRangeAndSections($id, $fields);
            $sections = $this->getAssessmentService()->drawItems($range, $sections);
            $sections = $this->setSectionQuestionScore($sections);
            $assessment = array(
                'bank_id' => $id,
                'name' => $fields['name'],
                'displayable' => 1,
                'description' => $fields['description'],
                'sections' => $sections,
            );
            $assessment = $this->getAssessmentService()->createAssessment($assessment);

            return $this->redirect(
                $this->generateUrl(
                    'question_bank_manage_testpaper_edit',
                    array('id' => $id, 'assessmentId' => $assessment['id'], 'showBaseInfo' => '0')
                )
            );
        }

        $types = $this->getQuestionTypes();

        $conditions = array(
            'bank_id' => $id,
        );

        $typesNum = $this->getItemService()->getItemCountGroupByTypes($conditions);
        $typesNum = ArrayToolkit::index($typesNum, 'type');
        $categoryTree = $this->getItemCategoryService()->getItemCategoryTree($questionBank['id']);

        return $this->render('question-bank/testpaper/random/testpaper-form.html.twig', array(
            'categoryTree' => $categoryTree,
            'types' => $types,
            'typesNum' => $typesNum,
            'questionBank' => $questionBank,
        ));
    }

    public function editAction(Request $request, $id, $assessmentId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $showBaseInfo = $request->query->get('showBaseInfo', '1');
        $questionBank = $this->getItemBankService()->getItemBank($id);
        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);

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

            $this->getAssessmentService()->updateAssessment($assessmentId, $assessment);

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

        $testpapers = $this->getAssessmentService()->findAssessmentsByIds($ids);
        if (empty($testpapers)) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getTestpaperService()->deleteTestpapers($ids);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id, $assessmentId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);

        if (empty($assessment) || $assessment['bank_id'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $this->getAssessmentService()->deleteAssessment($assessmentId);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id, $assessmentId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);
        if (empty($assessment) || $assessment['bank_id'] != $id) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getAssessmentService()->openAssessment($assessmentId);

        return $this->createJsonResponse(true);
    }

    public function closeAction(Request $request, $id, $assessmentId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);
        if (empty($assessment) || $assessment['bank_id'] != $id) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $this->getAssessmentService()->closeAssessment($assessmentId);

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

    public function exportAction(Request $request, $id, $assessmentId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);

        if (empty($assessment) || $assessment['bank_id'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $imgRootDir = $this->get('kernel')->getContainer()->getParameter('kernel.root_dir').'/../web';
        $fileName = $assessment['name'].'.docx';
        $baseDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $path = $baseDir.DIRECTORY_SEPARATOR.$fileName;

        $result = $this->getAssessmentService()->exportAssessment($assessmentId, $path, $imgRootDir);

        if (empty($result)) {
            return $this->createMessageResponse('info', '导出试卷为空', null, 3000, $this->generateUrl('question_bank_manage_testpaper_list', array('id' => $id)));
        }


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
            'bank_id' => $id,
            'nameLike' => $request->query->get('keyword', ''),
        );
        $totalCount = $this->getAssessmentService()->countAssessments($conditions);
        $conditions['status'] = 'open';
        $openCount = $this->getAssessmentService()->countAssessments($conditions);

        $pagination = new Paginator(
            $request,
            $openCount,
            10
        );

        $testPapers = $this->getAssessmentService()->searchAssessments(
            $conditions,
            array('created_time' => 'DESC'),
            $pagination->getOffsetCount(),
            $pagination->getPerPageCount()
        );

        foreach ($testPapers as &$testPaper) {
            $testPaper = ArrayToolkit::parts($testPaper, array(
                'id',
                'name',
                'total_score',
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
            $excludeIds = $conditions['exclude_ids'];
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
            'excludeIds' => empty($excludeIds) ? '' : $excludeIds,
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
        $bank = $this->getItemBankService()->getItemBank($id);
        if (empty($bank)) {
            throw $this->createAccessDeniedException();
        }

        $data = $request->request->all();
        list($range, $sections) = $this->getRangeAndSections($id, $data);

        $sections = $this->getAssessmentService()->drawItems($range, $sections);
        foreach ($sections as $section) {
            if (!empty($section['items']['miss'])) {
                return $this->createJsonResponse(false);
            }
        }

        return $this->createJsonResponse(true);
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
            $section['type'] = $section['items'][0]['type'];
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

    protected function getRangeAndSections($bankId, $fields)
    {
        $range = array(
            'bank_id' => $bankId,
            'category_ids' => empty($fields['ranges']['categoryId']) ? array() : array($fields['ranges']['categoryId']),
        );

        $sections = array();
        foreach ($fields['sections'] as $type => $section) {
            $section = array(
                'conditions' => array(
                    'item_types' => array($type),
                ),
                'item_count' => $section['count'],
                'name' => $section['name'],
                'score' => $fields['scores'][$type],
            );

            if (!empty($fields['missScores'][$type])) {
                $section['miss_score'] = $fields['missScores'][$type];
            }

            if ('difficulty' == $fields['mode']) {
                $section['conditions']['distribution'] = $fields['percentages'];
            }

            $sections[] = $section;
        }

        return array($range, $sections);
    }

    protected function setSectionQuestionScore($sections)
    {
        foreach ($sections as &$section) {
            foreach ($section['items'] as &$item) {
                foreach ($item['questions'] as &$question) {
                    $question['score'] = $section['score'];
                    if (!empty($section['miss_score'])) {
                        $question['miss_score'] = $section['miss_score'];
                    }
                }
            }
        }

        return $sections;
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
