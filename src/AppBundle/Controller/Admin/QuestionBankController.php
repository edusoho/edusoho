<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class QuestionBankController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->fillOrgCode($conditions);
        $count = $this->getQuestionBankService()->countQuestionBanks($conditions);
        $paginator = new Paginator($this->get('request'), $count, 20);
        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($questionBanks, 'categoryId'));
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin/question-bank/index.html.twig', array(
            'questionBanks' => $questionBanks,
            'categories' => $categories,
            'paginator' => $paginator,
            'categoryTree' => $categoryTree,
            'categoryId' => empty($conditions['categoryId']) ? 0 : $conditions['categoryId'],
        ));
    }

    public function createAction()
    {
    }

    public function editAction()
    {
    }

    public function deleteAction()
    {
    }

    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }
}
