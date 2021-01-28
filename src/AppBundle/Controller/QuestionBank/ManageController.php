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
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->hasPermission('admin_question_bank') && !$user->hasPermission('admin_v2_question_bank')) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['ids'] = ArrayToolkit::column($this->getQuestionBankService()->findUserManageBanks(), 'id');
        $conditions['ids'] = empty($conditions['ids']) ? [-1] : $conditions['ids'];
        $conditions = $this->fillOrgCode($conditions);

        $pagination = new Paginator(
            $request,
            $this->getQuestionBankService()->countQuestionBanks($conditions),
            20
        );

        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
            $conditions,
            ['createdTime' => 'DESC'],
            $pagination->getOffsetCount(),
            $pagination->getPerPageCount()
        );

        return $this->render('question-bank/list.html.twig', [
            'paginator' => $pagination,
            'questionBanks' => $questionBanks,
            'categoryTree' => $this->getCategoryService()->getCategoryTree(),
            'categoryId' => empty($conditions['categoryId']) ? 0 : $conditions['categoryId'],
        ]);
    }

    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $data['members'] = $this->getCurrentUser()->getId();
            $questionBank = $this->getQuestionBankService()->createQuestionBank($data);

            return $this->createJsonResponse([
                'goto' => $this->generateUrl('question_bank_manage_question_category', ['id' => $questionBank['id']]),
            ]);
        }

        return $this->render('question-bank/manage/create-modal.html.twig', [
            'categoryTree' => $this->getCategoryService()->getCategoryTree(),
        ]);
    }

    public function manageAction(Request $request, $id)
    {
        if (!$this->getQuestionBankService()->canManageBank($id)) {
            return $this->createMessageResponse('error', '您不是该题库管理者，不能查看此页面！');
        }

        if ($request->isMethod('POST')) {
            $questionBank = $this->getQuestionBankService()->updateQuestionBankWithMembers(
                $id,
                $request->request->all(),
                $request->request->get('members', '')
            );

            return $this->createJsonResponse([
                'goto' => $this->generateUrl('question_bank_manage_question_category', ['id' => $questionBank['id']]),
            ]);
        }

        $users = $this->getUserService()->findUsersByIds(
            ArrayToolkit::column($this->getMemberService()->findMembersByBankId($id), 'userId')
        );
        $bankMembers = [];
        foreach ($users as $user) {
            $bankMembers[] = ['id' => $user['id'], 'name' => $user['nickname']];
        }

        return $this->render('question-bank/manage/info.html.twig', [
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($id),
            'categoryTree' => $this->getCategoryService()->getCategoryTree(),
            'bankMembers' => json_encode($bankMembers),
        ]);
    }

    public function memberMatchAction(Request $request)
    {
        $queryField = $request->query->get('q');

        $users = $this->getUserService()->searchUsers(
            ['nickname' => $queryField, 'roles' => 'ROLE_TEACHER'],
            ['createdTime' => 'DESC'],
            0,
            10,
            ['id', 'nickname']
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
