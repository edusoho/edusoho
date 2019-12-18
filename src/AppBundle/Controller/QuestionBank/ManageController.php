<?php

namespace AppBundle\Controller\QuestionBank;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\QuestionBank\Service\QuestionBankService;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $categoryId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher() && !$user->hasPermission('admin_question_bank')) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = $request->query->all();
        $conditions['categoryId'] = empty($conditions['subCategory']) ? $categoryId : $conditions['subCategory'];
        $conditions['ids'] = ArrayToolkit::column($this->getQuestionBankService()->findUserManageBanks(), 'id');

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
}
