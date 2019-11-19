<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Question\Service\QuestionService;
use Symfony\Component\HttpFoundation\Request;
use Biz\QuestionBank\QuestionBankException;
use AppBundle\Common\Paginator;

class QuestionController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

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
        $questionCategories = $this->getQuestionCategoryService()->findCategories($questionBank['id']);
        $questionCategories = ArrayToolkit::index($questionCategories, 'id');

        return $this->render('question-bank/question/index.html.twig', array(
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $users,
            'questionBank' => $questionBank,
            'categories' => $categories,
            'parentQuestion' => $parentQuestion,
        ));
    }

    public function createAction(Request $request, $id, $type)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if ($request->isMethod('POST')) {
            $fields = $request->request->all();
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

    public function getQuestionsHtmlAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

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
        $questionCategories = $this->getQuestionCategoryService()->findCategories($questionBank['id']);
        $questionCategories = ArrayToolkit::index($questionCategories, 'id');

        return $this->render('question-bank/question/question-list-tr.html.twig', array(
            'questions' => $questions,
            'paginator' => $paginator,
            'users' => $users,
            'questionBank' => $questionBank,
            'questionCategories' => $questionCategories,
        ));
    }

    protected function getQuestionConfig()
    {
        return $this->get('extension.manager')->getQuestionTypes();
    }

    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

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
