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
        $categories = $this->getCategoryService()->findCategoriesByIds(
            ArrayToolkit::column($questionBanks, 'categoryId')
        );
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin/question-bank/index.html.twig', array(
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

        return $this->render('admin/question-bank/modal.html.twig', array(
            'questionBank' => $questionBank,
            'categoryTree' => $categoryTree,
        ));
    }

    public function editAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $questionBank = $this->getQuestionBankService()->updateQuestionBank($id, $request->request->all());

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

        return $this->render('admin/question-bank/modal.html.twig', array(
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

    public function memberMatchAction(Request $request)
    {
        $queryField = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(
            array('nickname' => $queryField, 'roles' => 'ROLE_TEACHER'),
            array('createdTime' => 'DESC'),
            0,
            10,
            array('id', 'nickname')
        );

        return $this->createJsonResponse($users);
    }

    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }
}
