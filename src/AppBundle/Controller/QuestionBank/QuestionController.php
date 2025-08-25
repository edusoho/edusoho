<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Biz\Question\QuestionParseClient;
use Biz\Question\Traits\QuestionAIAnalysisTrait;
use Biz\Question\Traits\QuestionImportTrait;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends BaseController
{
    use QuestionImportTrait;
    use QuestionAIAnalysisTrait;

    public function indexAction(Request $request, $id)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            return $this->createMessageResponse('error', 'exception.question_bank.not_found_bank', '', '30');
        }

        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['bank_id'] = $questionBank['itemBankId'];
        if (!empty($conditions['category_id'])) {
            $childrenIds = $this->getItemCategoryService()->findCategoryChildrenIds($conditions['category_id']);
            $childrenIds[] = $conditions['category_id'];
            $conditions['category_ids'] = $childrenIds;
            unset($conditions['category_id']);
        }

        $paginator = new Paginator(
            $request,
            $this->getItemService()->countItems($conditions),
            10
        );

        $items = $this->getItemService()->searchItems(
            $conditions,
            ['created_time' => 'ASC', 'id' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);
        $categoryTree = $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']);

        return $this->render('question-bank/question/index.html.twig', [
            'questions' => $items,
            'paginator' => $paginator,
            'users' => $this->getUserService()->findUsersByIds(array_merge(array_column($items, 'created_user_id'), array_column($items, 'updated_user_id'))),
            'questionBank' => $questionBank,
            'categoryTree' => $categoryTree,
            'categoryTreeArray' => $this->convertCategoryTreeToArray($categoryTree),
            'questionCategories' => ArrayToolkit::index($questionCategories, 'id'),
        ]);
    }

    public function importIntroAction()
    {
        return $this->render('question-bank/question/import-intro-modal.html.twig');
    }

    public function aiAnalysisIntroAction()
    {
        return $this->render('question-bank/question/ai-analysis-intro-modal.html.twig');
    }

    public function importAction(Request $request, $id)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        return $this->forward('AppBundle:Question/QuestionParser:read', [
            'request' => $request,
            'type' => 'item',
            'questionBank' => $questionBank,
        ]);
    }

    public function downloadImportTemplateAction(Request $request, $id, $type)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '没有题库管理权限');
        }
        $downloadUrl = $this->getQuestionParseClient()->getTemplateFileDownloadUrl($type, $request->isSecure());

        return $this->redirect($downloadUrl);
    }

    public function createAction(Request $request, $id, $type)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $categoryId = $request->query->get('categoryId', 0);

        $goto = $request->query->get('goto', $this->generateUrl('question_bank_manage_question_list', ['id' => $id]));
        $goto = $this->filterRedirectUrl($goto);

        if ($request->isMethod('POST')) {
            $fields = json_decode($request->getContent(), true);
            $fields['bank_id'] = $questionBank['itemBankId'];
            $fields['category_id'] = empty($fields['category_id']) ? $categoryId : $fields['category_id'];
            $item = $this->getItemService()->createItem($fields);

            if ('continue' === $fields['submission']) {
                $urlParams = ArrayToolkit::parts($item, ['difficulty']);
                $urlParams['id'] = $id;
                $urlParams['type'] = $type;
                $urlParams['goto'] = $goto;
                $goto = $this->generateUrl('question_bank_manage_question_create', $urlParams);
            }

            return $this->createJsonResponse(['goto' => $goto]);
        }

        return $this->render('question-manage/question-form-layout.html.twig', [
            'goto' => $goto,
            'mode' => 'create',
            'questionBank' => $questionBank,
            'type' => $type,
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']),
            'categoryId' => $categoryId,
        ]);
    }

    public function updateAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $item = $this->getItemService()->getItemWithQuestions($questionId, true);
        if (empty($item) || $item['bank_id'] != $questionBank['itemBankId']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $item = $this->wrapperItem($item);

        $goto = $request->query->get(
            'goto',
            $this->generateUrl('question_bank_manage_question_list', ['id' => $id])
        );
        $goto = $this->filterRedirectUrl($goto);

        if ($request->isMethod('POST')) {
            $content = $this->replaceFormulaToLocalImg($request->getContent());
            $this->getItemService()->updateItem($item['id'], json_decode($content, true));

            return $this->createJsonResponse(['goto' => $goto]);
        }
        $categoryId = $request->query->get('categoryId', 0);

        return $this->render('question-manage/question-form-layout.html.twig', [
            'mode' => 'edit',
            'questionBank' => $questionBank,
            'item' => $this->addArrayEmphasisStyle($item),
            'type' => $item['type'],
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($item['bank_id']),
            'goto' => $goto,
            'categoryId' => $categoryId,
        ]);
    }

    protected function wrapperItem($item)
    {
        foreach ($item['questions'] as &$question) {
            if ('text' == $question['answer_mode']) {
                foreach ($question['answer'] as $answer) {
                    $question['stem'] = preg_replace('/\[\[\]\]/', '[['.$answer.']]', $question['stem'], 1);
                }
            }
            if (!empty($question['score_rule'])) {
                $question['scoreType'] = $question['score_rule']['scoreType'];
                $question['otherScore'] = $question['score_rule']['otherScore'];
            }
            $question['aiAnalysisEnable'] = $this->canGenerateAIAnalysisForTeacher($question, $item);
        }

        return $item;
    }

    public function getQuestionsHtmlAction(Request $request, $id)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['bank_id'] = $questionBank['itemBankId'];

        $categoryId = 0;
        if (!empty($conditions['category_id'])) {
            $categoryId = $conditions['category_id'];
            $childrenIds = $this->getItemCategoryService()->findCategoryChildrenIds($conditions['category_id']);
            $childrenIds[] = $conditions['category_id'];
            $conditions['category_ids'] = $childrenIds;
            unset($conditions['category_id']);
        }

        $paginator = new Paginator(
            $request,
            $this->getItemService()->countItems($conditions),
            10
        );

        $questions = $this->getItemService()->searchItems(
            $conditions,
            ['created_time' => 'ASC', 'id' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);

        return $this->render('question-bank/question/question-list-table.html.twig', [
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $this->getUserService()->findUsersByIds(array_merge(array_column($questions, 'created_user_id'), array_column($questions, 'updated_user_id'))),
            'questionBank' => $questionBank,
            'categoryId' => $categoryId,
            'questionCategories' => ArrayToolkit::index($questionCategories, 'id'),
        ]);
    }

    public function deleteAction(Request $request, $id, $itemId)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $item = $this->getItemService()->getItem($itemId);
        if (!$item || $item['bank_id'] != $questionBank['itemBankId']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        if ($this->getItemService()->deleteItem($itemId)) {
            $user = $this->getCurrentUser();
            $this->getLogService()->info(
                'question_bank',
                'delete_question',
                $this->trans(
                    'admin.question_bank.manage.delete_question',
                    [
                        '%user%' => $user['nickname'],
                        '%questionBank%' => $questionBank['name'],
                        '%num%' => 1,
                    ]
                ),
                $item
            );
        }

        return $this->createJsonResponse(true);
    }

    public function checkQuestionDuplicativeAction(Request $request, $id)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }
        $data = json_decode($request->getContent(), true);

        if ($this->getItemService()->isMaterialDuplicative($questionBank['itemBankId'], $data['material'], $data['items'] ?? [], $data['itemId'] ?? 0)) {
            return $this->createJsonResponse(true);
        } else {
            return $this->createJsonResponse(false);
        }
    }

    public function checkDuplicativeQuestionsAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }
        $categoryId = $request->query->get('categoryId', '');
        if ($categoryId) {
            $category = $this->getItemCategoryService()->getItemCategory($categoryId);
        }

        return $this->render('question-manage/duplicative-questions.html.twig', [
            'questionBankId' => $id,
            'categoryId' => $categoryId,
            'categoryName' => $category['name'] ?? '',
        ]);
    }

    public function updateDuplicativeQuestionAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $item = $this->getItemService()->getItemWithQuestions($questionId, true);
        if (empty($item) || $item['bank_id'] != $questionBank['itemBankId']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $item = $this->wrapperItem($item);

        return $this->render('question-manage/update-duplicative-question.html.twig', [
            'questionBank' => $questionBank,
            'item' => $this->addArrayEmphasisStyle($item),
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($item['bank_id']),
            'goto' => $this->generateUrl('question_bank_manage_check_duplicative_questions', ['id' => $id]),
        ]);
    }

    public function deleteQuestionsAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }
        $ids = $request->request->get('ids', []);
        if (empty($this->getItemService()->findItemsByIds($ids))) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        if ($this->getItemService()->deleteItems($ids)) {
            $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
            $user = $this->getCurrentUser();
            $this->getLogService()->info(
                'question_bank',
                'delete_question',
                $this->trans(
                    'admin.question_bank.manage.delete_question',
                    [
                        '%user%' => $user['nickname'],
                        '%questionBank%' => $questionBank['name'],
                        '%num%' => count($ids),
                    ]
                ),
                $ids
            );
        }

        return $this->createJsonResponse(true);
    }

    public function setCategoryAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $ids = $request->request->get('ids', []);
        if (empty($this->getItemService()->findItemsByIds($ids))) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $categoryId = $request->request->get('categoryId', 0);

        $this->getItemService()->updateItemsCategoryId($ids, $categoryId);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $item = $this->getItemService()->getItemWithQuestions($questionId, true);

        if (!$item || $item['bank_id'] != $questionBank['itemBankId']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $item = $this->addArrayEmphasisStyle($item);

        $template = $request->query->get(
            'isNew'
        ) ? 'question-manage/preview.html.twig' : 'question-manage/preview-modal.html.twig';

        return $this->render($template, [
            'item' => $item,
        ]);
    }

    public function exportAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $bank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($bank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $conditions = $request->query->all();
        if (isset($conditions['ids']) && !empty($conditions['ids'])) {
            $conditions['ids'] = explode(',', $conditions['ids']);
        }
        $biz = $this->getBiz();
        $path = $biz['topxia.disk.local_directory'].DIRECTORY_SEPARATOR.Uuid::uuid4();

        $result = $this->getItemService()->exportItems(
            $bank['itemBankId'],
            $conditions,
            $path,
            $biz['kernel.root_dir'].'/../web'
        );

        if (empty($result)) {
            return $this->createMessageResponse(
                'info',
                '导出题目为空',
                null,
                3000,
                $this->generateUrl('question_bank_manage_question_list', ['id' => $id])
            );
        }

        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$this->getExportFileName($id),
        ];

        return new BinaryFileResponse($path, 200, $headers);
    }

    public function showQuestionTypesNumAction(Request $request)
    {
        $conditions = $request->request->all();
        if (empty($conditions['bankId'])) {
            return $this->createJsonResponse([]);
        }

        $bank = $this->getQuestionBankService()->getQuestionBank($conditions['bankId']);
        if (empty($bank)) {
            return $this->createJsonResponse([]);
        } else {
            $conditions['bank_id'] = $bank['itemBankId'];
        }

        $conditions = $this->filterConditions($conditions);

        $typesNum = $this->getItemService()->getItemCountGroupByTypes($conditions);
        $typesNum = ArrayToolkit::index($typesNum, 'type');

        return $this->createJsonResponse($typesNum);
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['categoryIds'])) {
            $conditions['category_ids'] = explode(',', $conditions['categoryIds']);
            unset($conditions['categoryIds']);
        }

        if (isset($conditions['categoryId']) && '' != $conditions['categoryId']) {
            //查询是否有子分类
            $categoryIds = $this->getItemCategoryService()->findCategoryChildrenIds((int) $conditions['categoryId']);
            if ($categoryIds) {
                $categoryIds[] = $conditions['categoryId'];
                $conditions['category_ids'] = $categoryIds;
            } else {
                $conditions['category_ids'] = [$conditions['categoryId']];
            }
            unset($conditions['categoryId']);
        }

        if (isset($conditions['difficulty']) && '0' == $conditions['difficulty']) {
            unset($conditions['difficulty']);
        }

        return $conditions;
    }

    protected function getExportFileName($id)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank['itemBank'])) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        return rawurlencode("{$questionBank['name']}-题目.docx");
    }

    protected function convertCategoryTreeToArray($categoryTree)
    {
        $categoryTreeArray = [];
        $push = function ($category) use (&$categoryTreeArray, &$push) {
            if (empty($category['children'])) {
                return;
            }
            foreach ($category['children'] as &$child) {
                array_push($categoryTreeArray, $child);
                $push($child);
                unset($child);
            }
        };
        $push(['children' => $categoryTree]);

        return $categoryTreeArray;
    }

    private function getQuestionParseClient()
    {
        return new QuestionParseClient();
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
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
}
