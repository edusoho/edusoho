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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuestionController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['bank_id'] = $id;

        $paginator = new Paginator(
            $this->get('request'),
            $this->getItemService()->countItems($conditions),
            10
        );

        $items = $this->getItemService()->searchItems(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($id);
        $categoryTree = $this->getItemCategoryService()->getItemCategoryTree($id);

        return $this->render('question-bank/question/index.html.twig', array(
            'questions' => $items,
            'paginator' => $paginator,
            'users' => $this->getUserService()->findUsersByIds(ArrayToolkit::column($items, 'updated_user_id')),
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($id),
            'categoryTree' => $categoryTree,
            'categoryTreeArray' => $this->convertCategoryTreeToArray($categoryTree),
            'questionCategories' => ArrayToolkit::index($questionCategories, 'id'),
        ));
    }

    public function importAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        return $this->forward('AppBundle:Question/QuestionParser:read', array(
            'request' => $request,
            'type' => 'question',
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($id),
        ));
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
            $fields['bank_id'] = $id;

            $item = $this->getItemService()->createItem($fields);

            $goto = $request->query->get('goto', $this->generateUrl('question_bank_manage_question_list', array('id' => $id)));
            if ('continue' === $fields['submission']) {
                $urlParams = ArrayToolkit::parts($item, array('difficulty'));
                $urlParams['id'] = $id;
                $urlParams['type'] = $type;
                $urlParams['goto'] = $goto;

                return $this->createJsonResponse(
                    array(
                        'goto' => $this->generateUrl('question_bank_manage_question_create', $urlParams)
                    )
                );
            }

            return $this->createJsonResponse(array('goto' => $goto));
        }

        return $this->render('question-manage/question-form-layout.html.twig', array(
            'mode' => 'create',
            'questionBank' => $questionBank,
            'type' => $type,
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($id),
        ));
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
        if (empty($item) || $item['bank_id'] != $questionBank['id']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $goto = $request->query->get(
            'goto',
            $this->generateUrl('question_bank_manage_question_list', array('id' => $id))
        );
        if ($request->isMethod('POST')) {
            $this->getItemService()->updateItem($item['id'], json_decode($request->getContent(), true));

            return $this->createJsonResponse(array('goto' => $goto));
        }

        return $this->render('question-manage/question-form-layout.html.twig', array(
            'mode' => 'edit',
            'questionBank' => $questionBank,
            'item' => $item,
            'type' => $item['type'],
            'categoryTree' => $this->getItemCategoryService()->getItemCategoryTree($id),
            'goto' => $goto,
        ));
    }

    public function getQuestionsHtmlAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['bank_id'] = $id;

        if (!empty($conditions['category_id'])) {
            $childrenIds = $this->getItemCategoryService()->findCategoryChildrenIds($conditions['category_id']);
            $childrenIds[] = $conditions['category_id'];
            $conditions['category_ids'] = $childrenIds;
            unset($conditions['category_id']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getItemService()->countItems($conditions),
            10
        );

        $questions = $this->getItemService()->searchItems(
            $conditions,
            array('created_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $questionCategories = $this->getItemCategoryService()->findItemCategoriesByBankId($questionBank['id']);

        return $this->render('question-bank/question/question-list-table.html.twig', array(
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'updated_user_id')),
            'questionBank' => $questionBank,
            'questionCategories' => ArrayToolkit::index($questionCategories, 'id'),
        ));
    }

    public function deleteAction(Request $request, $id, $itemId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $item = $this->getItemService()->getItem($itemId);
        if (!$item || $item['bank_id'] != $id) {
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

        $ids = $request->request->get('ids', array());
        $items = $this->getItemService()->findItemsByIds($ids);
        if (empty($items)) {
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

        $ids = $request->request->get('ids', array());
        $categoryId = $request->request->get('category_id', array());
        $items = $this->getItemService()->findItemsByIds($ids);
        if (empty($items)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $this->getItemService()->updateItemsCategoryId($ids, $categoryId);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $question = $this->getItemService()->getItem($questionId);

        if (!$question || $question['bank_id'] != $id) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $isNewWindow = $request->query->get('isNew');
        $template = $isNewWindow ? 'question-manage/preview.html.twig' : 'question-manage/preview-modal.html.twig';

        return $this->render($template, array(
            'question' => $question,
            'showAnswer' => 1,
            'showAnalysis' => 1,
        ));
    }

    public function exportAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $imgRootDir = $this->get('kernel')->getContainer()->getParameter('kernel.root_dir').'/../web';
        $baseDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $fileName = str_replace(',', '', $questionBank['name']).'-题目.docx';
        $path = $baseDir.DIRECTORY_SEPARATOR.$fileName;

        $result = $this->getItemService()->exportItems($id, $request->query->all(), $path, $imgRootDir);

        if (empty($result)) {
            return $this->createMessageResponse('info', '导出题目为空', null, 3000, $this->generateUrl('question_bank_manage_question_list', array('id' => $id)));
        }

        $headers = array(
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        );

        return new BinaryFileResponse($path, 200, $headers);
    }

    public function showQuestionTypesNumAction(Request $request)
    {
        $conditions = $request->request->all();
        if (!empty($conditions['categoryIds'])) {
            $conditions['category_ids'] = explode(',', $conditions['categoryIds']);
        }

        if (empty($conditions['bankId'])) {
            $conditions['bank_id'] = '-1';
        }

        $typesNum = $this->getItemService()->getItemCountGroupByTypes($conditions);
        $typesNum = ArrayToolkit::index($typesNum, 'type');

        return $this->createJsonResponse($typesNum);
    }

    protected function convertCategoryTreeToArray($categoryTree)
    {
        $categoryTreeArray = array();
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
        $push(array('children' => $categoryTree));

        return $categoryTreeArray;
    }

    protected function getQuestionConfig()
    {
        return $this->get('extension.manager')->getQuestionTypes();
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
