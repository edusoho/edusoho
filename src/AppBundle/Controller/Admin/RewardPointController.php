<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class RewardPointController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $userCount = $this->getUserService()->countUsers($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $userCount,
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (!empty($users)) {
            $userIds = ArrayToolkit::column($users, 'id');
            $conditions['userIds'] = $userIds;

            $accounts = $this->getAccountService()->searchAccounts(
                $conditions,
                array(),
                0,
                PHP_INT_MAX
            );

            $userProfiles = $this->getUserService()->searchUserProfiles(
                $conditions,
                array(),
                0,
                PHP_INT_MAX
            );

            $userProfiles = ArrayToolkit::index($userProfiles, 'id');
            $accounts = ArrayToolkit::index($accounts, 'userId');

            foreach ($accounts as &$account) {
                $accountOutFlow = $this->getAccountFlowService()->sumAccountOutFlowByUserId($account['userId']);
                $account['outFlow'] = $accountOutFlow;
            }
        }

        return $this->render('admin/reward-point/index.html.twig', array(
            'users' => $users,
            'userProfiles' => $userProfiles,
            'accounts' => $accounts,
            'paginator' => $paginator,
        ));
    }

    public function provideAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $account = $this->getAccountService()->getAccountByUserId($id);

        if ($request->getMethod() === 'POST') {
            $profile = $request->request->all();

            if (empty($account)) {
                $account = array(
                    'userId' => $id,
                    'balance' => $profile['amount'],
                );

                $account = $this->getAccountService()->createAccount($account);
            } else {
                $this->getAccountService()->waveBalance($account['id'], $profile['amount']);
            }

            $operator = $this->getCurrentUser();
            $flow = array(
                'userId' => $id,
                'type' => 'inflow',
                'amount' => $profile['amount'],
                'operator' => $operator['id'],
                'name' => '发放积分',
                'note' => $profile['note'],
            );

            $this->getAccountFlowService()->createAccountFlow($flow);

            return $this->redirect($this->generateUrl('admin_reward_point_account'));
        }

        return $this->render('admin/reward-point/provide-modal.html.twig',
            array(
                'user' => $user,
                'account' => $account,
            ));
    }

    public function detailAction(Request $request, $id)
    {
        $conditions['userId'] = $id;
        $user = $this->getUserService()->getUser($id);
        $accountFlowCount = $this->getAccountFlowService()->countAccountFlows($conditions);

        $paginator = new Paginator(
                $this->get('request'),
                $accountFlowCount,
                10
            );

        $accountFlows = $this->getAccountFlowService()->searchAccountFlows(
                $conditions,
                array('createdTime' => 'DESC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

        return $this->render('admin/reward-point/detail-modal.html.twig',
            array(
                'accountFlows' => $accountFlows,
                'user' => $user,
                'paginator' => $paginator,
            ));
    }

    public function deductionAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $account = $this->getAccountService()->getAccountByUserId($id);

        if ($request->getMethod() === 'POST') {
            $profile = $request->request->all();

            if (!empty($account)) {
                $this->getAccountService()->waveDownBalance($account['id'], $profile['amount']);
            }

            $operator = $this->getCurrentUser();
            $flow = array(
                'userId' => $id,
                'type' => 'outflow',
                'amount' => $profile['amount'],
                'operator' => $operator['id'],
                'name' => '扣减积分',
                'note' => $profile['note'],
            );
            $this->getAccountFlowService()->createAccountFlow($flow);

            return $this->redirect($this->generateUrl('admin_reward_point_account'));
        }

        return $this->render('admin/reward-point/deduction-modal.html.twig',
            array(
                'user' => $user,
                'account' => $account,
            ));
    }

    protected function getAccountService()
    {
        return $this->createService('RewardPoint:AccountService');
    }

    protected function getAccountFlowService()
    {
        return $this->createService('RewardPoint:AccountFlowService');
    }
}
