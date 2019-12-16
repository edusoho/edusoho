<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\QuestionException;
use Biz\Question\Service\CategoryService;
use Biz\Question\Service\QuestionService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ExamParser\Writer\WriteDocx;

class QuestionController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $conditions = $request->query->all();

        $conditions['bankId'] = $id;
        $conditions['parentId'] = empty($conditions['parentId']) ? 0 : $conditions['parentId'];

        $parentQuestion = array();
        $orderBy = array('createdTime' => 'DESC');
        if ($conditions['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->get($conditions['parentId']);
            $orderBy = array('createdTime' => 'ASC');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchCount($conditions),
            10
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'updatedUserId'));
        $categories = $this->getQuestionCategoryService()->getCategoryStructureTree($questionBank['id']);
        $categoryTree = $this->getQuestionCategoryService()->getCategoryTree($questionBank['id']);
        $questionCategories = $this->getQuestionCategoryService()->findCategories($questionBank['id']);
        $questionCategories = ArrayToolkit::index($questionCategories, 'id');

        return $this->render('question-bank/question/index.html.twig', array(
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $users,
            'questionBank' => $questionBank,
            'categories' => $categories,
            'categoryTree' => $categoryTree,
            'parentQuestion' => $parentQuestion,
            'questionCategories' => $questionCategories,
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
            'type' => 'question',
            'questionBank' => $questionBank,
        ));
    }

    public function createAction(Request $request, $id, $type)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $fields['bankId'] = $id;
            $question = $this->getQuestionService()->create($fields);

            $goto = $request->query->get('goto', null);
            if ('continue' === $fields['submission']) {
                $urlParams = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['id'] = $id;
                $urlParams['type'] = $type;
                $urlParams['goto'] = $goto;
                $this->setFlashMessage('success', 'site.add.success');

                return $this->redirect($this->generateUrl('question_bank_manage_question_create', $urlParams));
            }
            if ('continue_sub' === $fields['submission']) {
                $this->setFlashMessage('success', 'site.add.success');

                return $this->redirect(
                    $goto ?: $this->generateUrl(
                        'question_bank_manage_question_list',
                        array('id' => $id, 'parentId' => $question['id'])
                    )
                );
            }

            $this->setFlashMessage('success', 'site.add.success');

            return $this->redirect(
                $goto ?: $this->generateUrl(
                    'question_bank_manage_question_list',
                    array('id' => $id, 'parentId' => $question['parentId'])
                )
            );
        }

        $questionConfig = $this->getQuestionConfig();
        $createController = $questionConfig[$type]['actions']['create'];

        return $this->forward($createController, array(
            'request' => $request,
            'questionBankId' => $id,
            'type' => $type,
        ));
    }

    public function updateAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $question = $this->getQuestionService()->get($questionId);
        if (empty($question) || $question['bankId'] != $questionBank['id']) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
            $this->getQuestionService()->update($question['id'], $fields);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect(
                $request->query->get(
                    'goto',
                    $this->generateUrl(
                        'question_bank_manage_question_list',
                        array('id' => $id, 'parentId' => $question['parentId'])
                    )
                )
            );
        }

        $questionConfig = $this->getQuestionConfig();
        $editController = $questionConfig[$question['type']]['actions']['edit'];

        return $this->forward($editController, array(
            'request' => $request,
            'questionBankId' => $id,
            'questionId' => $question['id'],
        ));
    }

    public function getQuestionsHtmlAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);

        $conditions = $request->query->all();

        $conditions['bankId'] = $id;
        $conditions['parentId'] = empty($conditions['parentId']) ? 0 : $conditions['parentId'];

        $parentQuestion = array();
        $orderBy = array('createdTime' => 'DESC');
        if (!empty($conditions['parentId'])) {
            $parentQuestion = $this->getQuestionService()->get($conditions['parentId']);
            $orderBy = array('createdTime' => 'ASC');
        }

        if (!empty($conditions['categoryId'])) {
            $childrenIds = $this->getQuestionCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $childrenIds[] = $conditions['categoryId'];
            $conditions['categoryIds'] = $childrenIds;
            unset($conditions['categoryId']);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchCount($conditions),
            10
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'updatedUserId'));
        $questionCategories = $this->getQuestionCategoryService()->findCategories($questionBank['id']);
        $questionCategories = ArrayToolkit::index($questionCategories, 'id');

        return $this->render('question-bank/question/question-list-table.html.twig', array(
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $users,
            'questionBank' => $questionBank,
            'questionCategories' => $questionCategories,
            'parentQuestion' => $parentQuestion,
        ));
    }

    public function deleteAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $question = $this->getQuestionService()->get($questionId);
        if (!$question || $question['bankId'] != $id) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $this->getQuestionService()->delete($questionId);

        return $this->createJsonResponse(true);
    }

    public function deleteQuestionsAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $ids = $request->request->get('ids', array());
        $questions = $this->getQuestionService()->findQuestionsByIds($ids);
        if (empty($questions)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }
        $this->getQuestionService()->batchDeletes($ids);

        return $this->createJsonResponse(true);
    }

    public function setCategoryAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $ids = $request->request->get('ids', array());
        $categoryId = $request->request->get('categoryId', array());
        $questions = $this->getQuestionService()->findQuestionsByIds($ids);
        if (empty($questions)) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        $this->getQuestionService()->batchUpdateCategoryId($ids, $categoryId);

        return $this->createJsonResponse(true);
    }

    public function previewAction(Request $request, $id, $questionId)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            throw $this->createAccessDeniedException();
        }

        $isNewWindow = $request->query->get('isNew');

        $question = $this->getQuestionService()->get($questionId);

        if (!$question || $question['bankId'] != $id) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        if (!empty($question['matas']['mediaId'])) {
            $questionTypeObj = $this->getQuestionService()->getQuestionConfig($question['type']);
            $questionExtends = $questionTypeObj->get($question['matas']['mediaId']);
            $question = array_merge_recursive($question, $questionExtends);
        }

        if ($question['subCount'] > 0) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $question['subs'] = $questionSubs;
        }

        $template = 'question-manage/preview-modal.html.twig';
        if ($isNewWindow) {
            $template = 'question-manage/preview.html.twig';
        }

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
        $fields = $request->query->all();

        $conditions = ArrayToolkit::parts($fields, array('type', 'keyword', 'categoryId', 'difficulty'));
        $conditions['bankId'] = $id;
        $conditions['parentId'] = 0;

        $questionCount = $this->getQuestionService()->searchCount($conditions);

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            $questionCount
        );

        if (empty($questions)) {
            return $this->createMessageResponse('info', '导出题目为空', null, 3000, $this->generateUrl('course_set_manage_question', array('id' => $id)));
        }

        $questions = $this->buildExportQuestions($questions);

        $fileName = str_replace(',', '', $questionBank['name']).'-题目.docx';
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

    public function showQuestionTypesNumAction(Request $request)
    {
        $bankId = $request->request->get('bankId', 0);
        if (!empty($bankId) && !$this->getQuestionBankService()->canManageBank($bankId)) {
            throw $this->createAccessDeniedException();
        }

        $conditions = $request->request->all();
        $conditions['parentId'] = 0;
        $conditions['categoryIds'] = explode(',', $conditions['categoryIds']);

        $typesNum = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $typesNum = ArrayToolkit::index($typesNum, 'type');

        return $this->createJsonResponse($typesNum);
    }

    protected function buildExportQuestions($questions)
    {
        $exportQuestions = array();
        $wrapper = $this->getWrapper();

        $seq = 1;
        $num = 1;
        foreach ($questions as $question) {
            $question['seq'] = $seq++;
            $question['num'] = $num++;
            if ('material' == $question['type']) {
                $subQuestions = $this->getQuestionService()->findQuestionsByParentId($question['id']);
                $subSeq = 1;
                foreach ($subQuestions as $index => $subQuestion) {
                    $subQuestions[$index]['seq'] = $subSeq++;
                }
                $question['subs'] = $subQuestions;
            }

            $question = $wrapper->handle($question, 'exportQuestion');
            $question = ArrayToolkit::parts($question, array(
                'type',
                'seq',
                'stem',
                'options',
                'answer',
                'score',
                'difficulty',
                'analysis',
                'subs',
                'num',
            ));
            $exportQuestions[] = $question;
        }

        return $exportQuestions;
    }

    protected function getWrapper()
    {
        global $kernel;

        return $kernel->getContainer()->get('web.wrapper');
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
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
