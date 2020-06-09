<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $conditions = $request->query->all();
        $conditions['bank_id'] = $questionBank['itemBankId'];

        $paginator = new Paginator(
            $request,
            $this->getItemService()->countItems($conditions),
            10
        );

        $items = $this->getItemService()->searchItems(
            $conditions,
            ['created_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);
        $categoryTree = $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']);

        return $this->render('question-bank/question/index.html.twig', [
            'questions' => $items,
            'paginator' => $paginator,
            'users' => $this->getUserService()->findUsersByIds(ArrayToolkit::column($items, 'updated_user_id')),
            'questionBank' => $questionBank,
            'categoryTree' => $categoryTree,
            'categoryTreeArray' => $this->convertCategoryTreeToArray($categoryTree),
            'questionCategories' => ArrayToolkit::index($questionCategories, 'id'),
        ]);
    }

    public function importAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        return $this->forward('AppBundle:Question/QuestionParser:read', [
            'request' => $request,
            'type' => 'item',
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($id),
        ]);
    }

    public function createAction(Request $request, $id, $type)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if ($request->isMethod('POST')) {
            $fields = json_decode($request->getContent(), true);
            $fields['bank_id'] = $questionBank['itemBankId'];
            $item = $this->getItemService()->createItem($fields);

            $goto = $request->query->get('goto', $this->generateUrl('question_bank_manage_question_list', ['id' => $id]));
            if ('continue' === $fields['submission']) {
                $urlParams = ArrayToolkit::parts($item, ['difficulty']);
                $urlParams['id'] = $id;
                $urlParams['type'] = $type;
                $urlParams['goto'] = $goto;

                return $this->createJsonResponse(
                    [
                        'goto' => $this->generateUrl('question_bank_manage_question_create', $urlParams),
                    ]
                );
            }

            return $this->createJsonResponse(['goto' => $goto]);
        }

        return $this->render('question-manage/question-form-layout.html.twig', [
            'mode' => 'create',
            'questionBank' => $questionBank,
            'type' => $type,
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($questionBank['itemBankId']),
        ]);
    }

    public function updateAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
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
        if ($request->isMethod('POST')) {
            $this->getItemService()->updateItem($item['id'], json_decode($request->getContent(), true));

            return $this->createJsonResponse(['goto' => $goto]);
        }

        return $this->render('question-manage/question-form-layout.html.twig', [
            'mode' => 'edit',
            'questionBank' => $questionBank,
            'item' => $item,
            'type' => $item['type'],
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($item['bank_id']),
            'goto' => $goto,
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
        }

        return $item;
    }

    public function getQuestionsHtmlAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
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

        $questions = $this->getItemService()->searchItems(
            $conditions,
            ['created_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['itemBankId']);

        return $this->render('question-bank/question/question-list-table.html.twig', [
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'updated_user_id')),
            'questionBank' => $questionBank,
            'questionCategories' => ArrayToolkit::index($questionCategories, 'id'),
        ]);
    }

    public function deleteAction(Request $request, $id, $itemId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $item = $this->getItemService()->getItem($itemId);
        if (!$item || $item['bank_id'] != $questionBank['itemBankId']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $this->getItemService()->deleteItem($itemId);

        return $this->createJsonResponse(true);
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
        $this->getItemService()->deleteItems($ids);

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
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $item = $this->getItemService()->getItemWithQuestions($questionId, true);

        if (!$item || $item['bank_id'] != $questionBank['itemBankId']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $template = $request->query->get('isNew') ? 'question-manage/preview.html.twig' : 'question-manage/preview-modal.html.twig';

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
        $fileName = $this->getExportFileName($id);
        $path = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory').DIRECTORY_SEPARATOR.$fileName;

        $conditions = $request->query->all();
        if (isset($conditions['ids']) && !empty($conditions['ids'])) {
            $conditions['ids'] = explode(',', $conditions['ids']);
        }

        $result = $this->getItemService()->exportItems(
            $bank['itemBankId'],
            $conditions,
            $path,
            $this->get('kernel')->getContainer()->getParameter('kernel.root_dir').'/../web'
        );

        if (empty($result)) {
            return $this->createMessageResponse('info', '导出题目为空', null, 3000, $this->generateUrl('question_bank_manage_question_list', ['id' => $id]));
        }

        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$fileName,
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
            $conditions['category_ids'] = [$conditions['categoryId']];
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
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        return str_replace(',', '', $questionBank['name']).'-题目.docx';
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
