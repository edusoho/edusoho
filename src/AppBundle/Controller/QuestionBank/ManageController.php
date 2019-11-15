<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $category)
    {
        $conditions = $request->query->all();

        list($conditions, $categoryArray, $categoryParent) = $this->mergeConditionsByCategory($conditions, $category);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionBankService()->countQuestionBanks($conditions),
            20
        );

        list($conditions, $orderBy) = $this->getQuestionBankSearchOrderBy($conditions);

        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('question-bank/list.html.twig', array(
            'category' => $category,
            'questionBanks' => $questionBanks
        ));
    }

    protected function getQuestionBankSearchOrderBy($conditions)
    {
        $orderBy = 'latest';

        $orderBy = empty($conditions['orderBy']) ? $orderBy : $conditions['orderBy'];
        unset($conditions['orderBy']);

        return array($conditions, $orderBy);
    }

    protected function mergeConditionsByCategory($conditions, $category)
    {
        $categoryArray = array();
        $subCategory = empty($conditions['subCategory']) ? null : $conditions['subCategory'];

        if (!empty($subCategory)) {
            $conditions['categoryId'] = $subCategory;
        } else {
            $conditions['categoryId'] = $category;
        }

        $categoryArray = $this->getCategoryService()->getCategory($conditions['categoryId']);

        $categoryParent = array();
        if (!empty($categoryArray['parentId'])) {
            $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
        }

        return array($conditions, $categoryArray, $categoryParent);
    }

    public function manageAction()
    {
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
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }
}
