<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

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
            array('id' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $categories = $this->getCategoryService()->findCategoriesByIds(
            ArrayToolkit::column($questionBanks, 'categoryId')
        );
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin-v2/teach/question-bank/index.html.twig', array(
            'questionBanks' => $questionBanks,
            'categories' => $categories,
            'paginator' => $paginator,
            'categoryTree' => $categoryTree,
            'categoryId' => empty($conditions['categoryId']) ? 0 : $conditions['categoryId'],
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $questionBank = $this->getQuestionBankService()->createQuestionBank($request->request->all());

            return $this->createJsonResponse($questionBank);
        }

        $questionBank = array(
            'id' => 0,
            'name' => '',
            'categoryId' => 0,
        );
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin-v2/teach/question-bank/modal.html.twig', array(
            'questionBank' => $questionBank,
            'categoryTree' => $categoryTree,
        ));
    }

    public function editAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $members = $request->request->get('members', '');
            $questionBank = $this->getQuestionBankService()->updateQuestionBankWithMembers($id, $request->request->all(), $members);

            return $this->createJsonResponse($questionBank);
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $members = $this->getMemberService()->findMembersByBankId($id);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));
        $bankMembers = array();
        foreach ($users as $user) {
            $bankMembers[] = array('id' => $user['id'], 'name' => $user['nickname']);
        }
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin-v2/teach/question-bank/modal.html.twig', array(
            'questionBank' => $questionBank,
            'categoryTree' => $categoryTree,
            'bankMembers' => json_encode($bankMembers),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $questionBank = $this->getQuestionBankService()->deleteQuestionBank($id);

        return $this->createJsonResponse($questionBank);
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

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }
}
