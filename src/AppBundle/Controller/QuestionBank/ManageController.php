<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $category)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = $request->query->all();

        if (!$user->isSuperAdmin()) {
            $members = $this->getMemberService()->findMembersByUserId($user->getId());
            $questionBankIds = ArrayToolkit::column($members, 'bankId');
            $conditions['ids'] = $questionBankIds ? $questionBankIds : array(-1);
        }

        list($conditions, $categoryArray, $categoryParent) = $this->mergeConditionsByCategory($conditions, $category);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionBankService()->countQuestionBanks($conditions),
            20
        );

        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        return $this->render('question-bank/list.html.twig', array(
            'category' => $category,
            'questionBanks' => $questionBanks,
        ));
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
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }
}
