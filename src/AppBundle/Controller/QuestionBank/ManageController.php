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
    public function indexAction(Request $request, $categoryId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->hasPermission('admin_question_bank') && !$user->hasPermission('admin_v2_question_bank')) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['categoryId'] = empty($conditions['subCategory']) ? $categoryId : $conditions['subCategory'];
        $conditions['ids'] = ArrayToolkit::column($this->getQuestionBankService()->findUserManageBanks(), 'id');
        $conditions['ids'] = empty($conditions['ids']) ? array(-1) : $conditions['ids'];
        $conditions = $this->fillOrgCode($conditions);

        $pagination = new Paginator(
            $request,
            $this->getQuestionBankService()->countQuestionBanks($conditions),
            20
        );

        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
            $conditions,
            array('createdTime' => 'DESC'),
            $pagination->getOffsetCount(),
            $pagination->getPerPageCount()
        );

        return $this->render('question-bank/list.html.twig', array(
            'category' => $categoryId,
            'paginator' => $pagination,
            'questionBanks' => $questionBanks,
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $data['members'] = $this->getCurrentUser()->getId();
            $questionBank = $this->getQuestionBankService()->createQuestionBank($data);

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('question_bank_manage_question_category', array('id' => $questionBank['id'])),
            ));
        }

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('question-bank/manage/create-modal.html.twig', array(
            'categoryTree' => $categoryTree,
        ));
    }

    public function manageAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        if ('POST' == $request->getMethod()) {
            $members = $request->request->get('members', '');
            $questionBank = $this->getQuestionBankService()->updateQuestionBankWithMembers($id, $request->request->all(), $members);

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('question_bank_manage_question_category', array('id' => $questionBank['id'])),
            ));
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($id);
        $members = $this->getMemberService()->findMembersByBankId($id);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));
        $bankMembers = array();
        foreach ($users as $user) {
            $bankMembers[] = array('id' => $user['id'], 'name' => $user['nickname']);
        }
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('question-bank/manage/info.html.twig', array(
            'questionBank' => $questionBank,
            'categoryTree' => $categoryTree,
            'bankMembers' => json_encode($bankMembers),
        ));
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
