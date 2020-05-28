<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class QuestionBankController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $this->fillOrgCode($request->query->all());
        $paginator = new Paginator($request, $this->getQuestionBankService()->countQuestionBanks($conditions), 20);
        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(
            $conditions,
            ['id' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/teach/question-bank/index.html.twig', [
            'questionBanks' => $questionBanks,
            'categories' => $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($questionBanks, 'categoryId')),
            'paginator' => $paginator,
            'categoryTree' => $this->getCategoryService()->getCategoryTree(),
            'categoryId' => empty($conditions['categoryId']) ? 0 : $conditions['categoryId'],
        ]);
    }

    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $questionBank = $this->getQuestionBankService()->createQuestionBank($request->request->all());

            return $this->createJsonResponse($questionBank);
        }

        $questionBank = [
            'id' => 0,
            'name' => '',
            'categoryId' => 0,
        ];

        return $this->render('admin-v2/teach/question-bank/modal.html.twig', [
            'questionBank' => $questionBank,
            'categoryTree' => $this->getCategoryService()->getCategoryTree(),
        ]);
    }

    public function editAction(Request $request, $id)
    {
        if ($request->isMethod('POST')) {
            $questionBank = $this->getQuestionBankService()->updateQuestionBankWithMembers(
                $id,
                $request->request->all(),
                $request->request->get('members', '')
            );

            return $this->createJsonResponse($questionBank);
        }

        $users = $this->getUserService()->findUsersByIds(
            ArrayToolkit::column($this->getMemberService()->findMembersByBankId($id), 'userId')
        );
        $bankMembers = [];
        foreach ($users as $user) {
            $bankMembers[] = ['id' => $user['id'], 'name' => $user['nickname']];
        }

        return $this->render('admin-v2/teach/question-bank/modal.html.twig', [
            'questionBank' => $this->getQuestionBankService()->getQuestionBank($id),
            'categoryTree' => $this->getCategoryService()->getCategoryTree(),
            'bankMembers' => json_encode($bankMembers),
        ]);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getQuestionBankService()->deleteQuestionBank($id);

        return $this->createJsonResponse(true);
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
