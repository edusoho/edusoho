<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Biz\QuestionBank\QuestionBankException;
use AppBundle\Common\Paginator;
use Biz\Question\QuestionException;

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

    public function deleteQuestionsAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->validateCanManageBank($id)) {
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

    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
